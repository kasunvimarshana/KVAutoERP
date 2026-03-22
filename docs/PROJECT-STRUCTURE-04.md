app/
  Domain/               # Enterprise business rules
    Product/
      Entities/         # Product.php (business logic)
      ValueObjects/     # Money.php, SKU.php
      RepositoryInterfaces/ # ProductRepositoryInterface.php
      Events/           # ProductCreated.php
  Application/          # Application business rules
    Product/
      UseCases/         # CreateProduct.php, UpdateStock.php
      DTOs/             # ProductData.php
  Infrastructure/       # Frameworks, drivers, external concerns
    Persistence/
      Eloquent/
        Models/         # ProductModel.php (implements Domain entity)
        Repositories/   # EloquentProductRepository.php
    Http/
      Controllers/      # ProductController.php (thin)
      Requests/         # StoreProductRequest.php
      Resources/        # ProductResource.php
    Messaging/          # Kafka/ProductEventProducer.php
