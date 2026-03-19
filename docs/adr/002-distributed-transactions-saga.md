# ADR 002: Distributed Transaction Management via Saga Pattern

## Status
Accepted

## Context
Our system is microservice-driven, with separate databases for Product, Inventory, and Order services. Traditional 2PC (Two-Phase Commit) is not suitable for horizontally scalable, distributed SaaS platforms due to locking and performance bottlenecks.

## Decision
We will use the **Saga Pattern** with an orchestrator to manage distributed transactions.
- **Orchestration**: The `shared-core` package provides a `SagaOrchestrator` to coordinate steps.
- **Compensating Actions**: Each step must implement a `rollback()` method to revert changes in case of downstream failures.
- **Asynchronous & Synchronous**: While our initial implementation uses synchronous HTTP calls via `ExternalServiceClient`, the orchestrator is designed to support asynchronous messaging (Kafka/RabbitMQ) for higher resilience.

## Consequences
- **Loose Coupling**: Services only interact via APIs or events.
- **Resilience**: Failures in one service (e.g., Payment) trigger automatic rollbacks in others (e.g., Inventory).
- **Complexity**: Developers must write explicit compensation logic for every state-changing step.
- **Eventual Consistency**: The system is eventually consistent, requiring audit logs and health checks for reconciliation.
