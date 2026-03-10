"""
SQLAlchemy ORM models for the Payment Service.
"""

import enum
from datetime import datetime, timezone
from sqlalchemy import Column, Integer, String, Float, DateTime, Enum as SAEnum
from app.database import Base


class PaymentStatus(str, enum.Enum):
    PENDING   = "pending"
    COMPLETED = "completed"
    FAILED    = "failed"
    REFUNDED  = "refunded"


class Payment(Base):
    __tablename__ = "payments"

    id         = Column(Integer, primary_key=True, index=True)
    order_id   = Column(Integer, nullable=False, index=True)
    user_id    = Column(Integer, nullable=False)
    amount     = Column(Float, nullable=False)
    status     = Column(SAEnum(PaymentStatus), default=PaymentStatus.PENDING, nullable=False)
    reason     = Column(String(512), nullable=True)
    created_at = Column(DateTime(timezone=True), default=lambda: datetime.now(timezone.utc))
    updated_at = Column(
        DateTime(timezone=True),
        default=lambda: datetime.now(timezone.utc),
        onupdate=lambda: datetime.now(timezone.utc),
    )
