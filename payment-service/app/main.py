"""Payment Service – FastAPI application."""

import asyncio
import logging
import os
from contextlib import asynccontextmanager

from fastapi import FastAPI, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select

from app.database import get_db, init_db
from app.models import Payment, PaymentStatus
from app.consumers.order_consumer import start_consumer

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


# ─────────────────────────────────────────────
# Lifespan: init DB + start consumer
# ─────────────────────────────────────────────
@asynccontextmanager
async def lifespan(app: FastAPI):
    await init_db()
    asyncio.create_task(start_consumer())
    logger.info("[payment-service] Ready.")
    yield


app = FastAPI(title="Payment Service", version="1.0.0", lifespan=lifespan)


# ─────────────────────────────────────────────
# Schemas
# ─────────────────────────────────────────────
class PaymentCreate(BaseModel):
    order_id: int
    user_id:  int
    amount:   float


class PaymentOut(BaseModel):
    id:        int
    order_id:  int
    user_id:   int
    amount:    float
    status:    PaymentStatus

    class Config:
        from_attributes = True


# ─────────────────────────────────────────────
# Routes
# ─────────────────────────────────────────────
@app.get("/health")
async def health():
    return {"status": "ok", "service": "payment-service", "version": "1.0.0"}


@app.get("/api/payments", response_model=list[PaymentOut])
async def list_payments(db: AsyncSession = Depends(get_db)):
    result = await db.execute(select(Payment).order_by(Payment.id.desc()).limit(50))
    return result.scalars().all()


@app.get("/api/payments/{payment_id}", response_model=PaymentOut)
async def get_payment(payment_id: int, db: AsyncSession = Depends(get_db)):
    payment = await db.get(Payment, payment_id)
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    return payment


@app.get("/api/payments/order/{order_id}", response_model=list[PaymentOut])
async def get_payments_by_order(order_id: int, db: AsyncSession = Depends(get_db)):
    result = await db.execute(
        select(Payment).where(Payment.order_id == order_id)
    )
    return result.scalars().all()


@app.post("/api/payments", response_model=PaymentOut, status_code=201)
async def create_payment(data: PaymentCreate, db: AsyncSession = Depends(get_db)):
    """
    Direct payment creation endpoint (for testing / admin use).
    The normal flow uses the Saga consumer.
    """
    payment = Payment(
        order_id=data.order_id,
        user_id=data.user_id,
        amount=data.amount,
        status=PaymentStatus.PENDING,
    )
    db.add(payment)
    await db.commit()
    await db.refresh(payment)
    return payment
