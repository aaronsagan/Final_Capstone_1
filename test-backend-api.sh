#!/bin/bash

echo "🔧 Testing Backend API Connectivity..."
echo ""

# Test if backend server is running
echo "1. Testing backend server (http://127.0.0.1:8000)..."
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000 > /dev/null 2>&1; then
    echo "   ✅ Backend server is running"
else
    echo "   ❌ Backend server is not running"
    echo "   💡 Run: cd capstone_backend && php artisan serve"
    exit 1
fi

# Test API ping endpoint
echo ""
echo "2. Testing API ping endpoint..."
if curl -s http://127.0.0.1:8000/api/ping | grep -q "ok"; then
    echo "   ✅ API ping endpoint working"
else
    echo "   ❌ API ping endpoint failed"
fi

# Test metrics endpoint (public)
echo ""
echo "3. Testing metrics endpoint..."
if curl -s http://127.0.0.1:8000/api/metrics | head -1 | grep -q "{"; then
    echo "   ✅ Metrics endpoint working"
else
    echo "   ❌ Metrics endpoint failed"
fi

echo ""
echo "✅ Backend API tests complete!"
echo ""
echo "If all tests pass, try creating a post again."
echo "If posting still fails, check the browser console for detailed error messages."
