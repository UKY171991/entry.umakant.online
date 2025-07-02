#!/bin/bash

echo "Testing Laravel Application Endpoints..."

# Test basic pages
echo "1. Testing Dashboard..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/dashboard || echo "Dashboard test failed"

echo "2. Testing Clients page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/clients || echo "Clients test failed"

echo "3. Testing Income page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/incomes || echo "Income test failed"

echo "4. Testing Expenses page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/expenses || echo "Expenses test failed"

echo "5. Testing Emails page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/emails || echo "Emails test failed"

echo "6. Testing Websites page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/websites || echo "Websites test failed"

echo "7. Testing Pending Tasks page..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/pending-tasks || echo "Pending Tasks test failed"

echo "All tests completed!"
