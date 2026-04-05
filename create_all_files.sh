#!/bin/bash
set -e

# Create all module files
for module_path in app/Modules/{Tenant,Auth,Configuration}/Domain/Entities \
                   app/Modules/{Tenant,Auth,Configuration}/Domain/RepositoryInterfaces \
                   app/Modules/{Tenant,Auth,Configuration}/Application/Services \
                   app/Modules/{Tenant,Auth,Configuration}/Infrastructure/Persistence/Eloquent/{Models,Repositories} \
                   app/Modules/{Tenant,Auth,Configuration}/Infrastructure/Providers \
                   app/Modules/{Tenant,Auth,Configuration}/database/migrations \
                   bootstrap; do
  mkdir -p "$module_path"
done

echo "Directories created successfully"
