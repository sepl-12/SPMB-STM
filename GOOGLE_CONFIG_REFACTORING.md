# Google Services Configuration Refactoring

## 📋 Overview

Refactoring ini menghilangkan hard-coded `env()` calls dari service classes dan menggantinya dengan proper dependency injection melalui config files. Ini membuat kode lebih testable, mengikuti Laravel best practices, dan mencegah bugs saat config caching.

## 🎯 Masalah yang Diselesaikan

### Sebelum Refactoring:
```php
// ❌ BAD - Hard-coded env() in service class
class GmailApiService
{
    private function googleClient(): Client
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        // ...
    }
}
```

**Masalah:**
- ❌ Tidak bisa di-mock untuk testing
- ❌ Config caching akan break (env() returns null)
- ❌ Tight coupling ke global state
- ❌ Violates Dependency Injection principle

### Setelah Refactoring:
```php
// ✅ GOOD - Config injection via constructor
class GmailApiService
{
    public function __construct(
        private readonly array $config
    ) {}
    
    private function googleClient(): Client
    {
        $clientId = $this->config['client_id'];
        $clientSecret = $this->config['client_secret'];
        // ...
    }
}
```

**Benefits:**
- ✅ Fully testable dengan fake config
- ✅ Config caching works perfectly
- ✅ Explicit dependencies
- ✅ Follows SOLID principles

## 📁 Files Changed

### 1. New Config File
- **File:** `config/google.php`
- **Purpose:** Centralize all Google OAuth and Gmail API configuration
- **Content:**
  ```php
  return [
      'client_id' => env('GOOGLE_CLIENT_ID'),
      'client_secret' => env('GOOGLE_CLIENT_SECRET'),
      'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
      'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
      'sender_email' => env('GOOGLE_SENDER'),
      'scopes' => ['https://www.googleapis.com/auth/gmail.send'],
  ];
  ```

### 2. Refactored Services

#### `app/Services/GmailApiService.php`
- Added constructor with `$config` parameter
- Replaced `env()` calls with `$this->config['key']`
- Added support for configurable scopes

#### `app/Services/Email/GmailEmailService.php`
- Added constructor with `$config` parameter
- Updated `isHealthy()` to use config instead of `env()`

#### `app/Services/GmailMailableSender.php`
- Added constructor with `$config` parameter
- Replaced `env('GOOGLE_SENDER')` with `$this->config['sender_email']`

#### `app/Http/Controllers/GoogleOauthController.php`
- Added constructor with `$config` parameter
- Replaced all `env()` calls with config values
- Added support for configurable scopes

### 3. Service Provider Bindings

#### `app/Providers/AppServiceProvider.php`
Added proper dependency injection bindings:

```php
// Bind GmailApiService with config
$this->app->singleton(\App\Services\GmailApiService::class, function ($app) {
    return new \App\Services\GmailApiService(config('google'));
});

// Bind GmailMailableSender with config
$this->app->singleton(GmailMailableSender::class, function ($app) {
    return new GmailMailableSender(
        $app->make(\App\Services\GmailApiService::class),
        config('google')
    );
});

// Bind GmailEmailService with config
$this->app->singleton(GmailEmailService::class, function ($app) {
    return new GmailEmailService(
        $app->make(GmailMailableSender::class),
        config('google')
    );
});

// Bind controller with config
$this->app->when(\App\Http\Controllers\GoogleOauthController::class)
    ->needs('$config')
    ->give(fn() => config('google'));
```

### 4. New Unit Tests

#### `tests/Unit/Services/GmailApiServiceTest.php`
Tests for GmailApiService:
- ✅ Can instantiate with config
- ✅ Throws exception when refresh token empty
- ✅ Base64URL encoding works correctly
- ✅ Can be resolved from container

#### `tests/Unit/Services/Email/GmailEmailServiceTest.php`
Tests for GmailEmailService:
- ✅ Can instantiate with config
- ✅ `isHealthy()` returns true with valid config
- ✅ `isHealthy()` returns false with missing credentials
- ✅ Service name returns correct value
- ✅ Can be resolved from container

## 🧪 Testing

### Running Unit Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Services/GmailApiServiceTest.php

# Run with coverage
php artisan test --coverage
```

### Example: Testing with Fake Config

```php
public function test_gmail_service_with_fake_config(): void
{
    $fakeConfig = [
        'client_id' => 'fake-client-id',
        'client_secret' => 'fake-secret',
        'refresh_token' => 'fake-token',
        'sender_email' => 'test@example.com',
    ];
    
    $service = new GmailApiService($fakeConfig);
    
    // Now you can test without real credentials!
    $this->assertInstanceOf(GmailApiService::class, $service);
}
```

## 🚀 Migration Guide

### For Existing Code

**No changes needed!** All existing code will continue to work because:
1. Services are still bound in the container
2. Config values come from the same `.env` variables
3. Backward compatibility maintained with fallback to `config()` helper

### For New Code

When creating new services that need Google config:

```php
class YourNewService
{
    public function __construct(
        private readonly array $googleConfig
    ) {}
    
    public function doSomething(): void
    {
        $clientId = $this->googleConfig['client_id'];
        // Use config values, never env() directly
    }
}

// In AppServiceProvider
$this->app->singleton(YourNewService::class, function ($app) {
    return new YourNewService(config('google'));
});
```

## ⚡ Performance

### Config Caching

Now you can safely use config caching in production:

```bash
# Cache config for production
php artisan config:cache

# Clear config cache
php artisan config:clear
```

**Before refactoring:** `env()` calls would return `null` after caching ❌  
**After refactoring:** Config values are cached and work perfectly ✅

## 🔒 Security

### Best Practices

1. **Never commit `.env` file** - Keep credentials secret
2. **Use environment-specific configs:**
   - `.env` for local development
   - `.env.production` for production
   - Use secret managers (AWS Secrets, Azure Key Vault) for production

3. **Rotate tokens regularly** - Refresh tokens should be rotated periodically

## 📚 Related Documentation

- [Laravel Configuration Docs](https://laravel.com/docs/configuration)
- [Laravel Service Container](https://laravel.com/docs/container)
- [Laravel Testing](https://laravel.com/docs/testing)

## ✅ Checklist

After deploying this refactoring:

- [x] Create `config/google.php` file
- [x] Refactor all services to use config injection
- [x] Update AppServiceProvider bindings
- [x] Create unit tests
- [x] Test config caching works
- [ ] Update production deployment scripts (if needed)
- [ ] Test in staging environment
- [ ] Deploy to production

## 🐛 Troubleshooting

### Issue: "Class config not found"

**Solution:** Make sure you've created `config/google.php` and it returns an array.

### Issue: "Undefined array key 'client_id'"

**Solution:** Check your `.env` file has all required Google variables:
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-secret
GOOGLE_REFRESH_TOKEN=your-refresh-token
GOOGLE_REDIRECT_URI=http://localhost/oauth/callback
GOOGLE_SENDER=your-email@gmail.com
```

### Issue: Tests failing with "OAuth refresh gagal"

**Solution:** In tests, mock the service or provide fake config:
```php
$this->mock(GmailApiService::class, function ($mock) {
    $mock->shouldReceive('sendRaw')->andReturn('fake-message-id');
});
```

## 👥 Credits

Refactored as part of improving testability and following Laravel best practices.

---

**Last Updated:** October 27, 2025
