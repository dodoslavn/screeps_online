# Security Improvements

This document details the security vulnerabilities that were fixed in the 2.0 refactoring.

## Critical Vulnerabilities Fixed

### 1. SQL Injection ✅

**Before:**
```php
$sql = mysqli_query($conn, "SELECT * FROM users WHERE name = '".$username."'");
```

**After:**
```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$username]);
```

**Impact**: All database queries now use prepared statements with parameterized queries, eliminating SQL injection risk.

**Files Affected**: All 50+ database queries across the application.

### 2. Weak Password Hashing ✅

**Before:**
```php
$password = crypt($password, 'screeps'); // Fixed salt!
```

**After:**
```php
password_hash($password, PASSWORD_ARGON2ID); // Secure, salted
```

**Impact**:
- Uses Argon2ID (or BCrypt fallback) instead of weak crypt()
- Unique salt per password
- Automatic cost/memory parameters
- Password rehashing on algorithm upgrades

**Migration**: Users with old passwords must reset via password reset feature.

### 3. Cross-Site Scripting (XSS) ✅

**Before:**
```php
echo "<td>".$row['name']."</td>"; // No escaping!
```

**After:**
```php
<td><?= e($row['name']) ?></td>  // Escaped with htmlspecialchars()
```

**Impact**: All user-controlled output is escaped, preventing XSS attacks.

**Helper Function**:
```php
function e($string): string {
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

### 4. Cross-Site Request Forgery (CSRF) ✅

**Before:**
- No CSRF protection on any forms

**After:**
```php
// Generation
$token = $csrf->generateToken();

// Validation (automatic via middleware)
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
```

**Impact**:
- All POST requests require valid CSRF token
- Tokens expire after 1 hour
- Constant-time comparison prevents timing attacks

### 5. Session Security ✅

**Before:**
```php
session_start(); // No security options
$_SESSION['account'] = $username; // No regeneration
```

**After:**
```php
session_set_cookie_params([
    'secure' => true,      // HTTPS only
    'httponly' => true,    // No JavaScript access
    'samesite' => 'Strict' // CSRF protection
]);
session_regenerate_id(true); // Prevent fixation
```

**Impact**:
- Sessions timeout after 2 hours
- Session ID regenerates on login
- Periodic regeneration every 30 minutes
- Secure cookie flags

### 6. Input Validation ✅

**Before:**
```php
if (!is_numeric($port)) $check = false; // Minimal validation
```

**After:**
```php
public function validateServerAddress(string $address): array {
    // Comprehensive validation:
    // - Format check (host:port)
    // - Port range (1024-65535)
    // - IP validation
    // - Hostname validation
    // - Private IP blocking
    return $result;
}
```

**Impact**: Comprehensive server-side validation for all inputs.

### 7. Rate Limiting ✅

**Before:**
- No rate limiting
- Basic session-based attempt counter (easily bypassed)

**After:**
```php
// 5 login attempts per 15 minutes
$rateLimiter = new RateLimitMiddleware('login', 5, 900);

// 3 registration attempts per hour
$rateLimiter = new RateLimitMiddleware('register', 3, 3600);
```

**Impact**: Prevents brute force attacks on login, registration, and password reset.

### 8. Information Disclosure ✅

**Before:**
```php
if (!$conn) die("database"); // Exposes server info
echo "SQL: " . $query; // Debug output in production
```

**After:**
```php
// Generic error messages to users
// Detailed errors logged to file
logMessage('Database error: ' . $e->getMessage(), 'ERROR');

// Different behavior for dev/production
if (isDebug()) {
    throw $e; // Dev: show full error
} else {
    echo "An error occurred"; // Production: generic message
}
```

### 9. Race Conditions ✅

**Before:**
```php
// Check if claimed
$sql = "SELECT user_id FROM server_info WHERE ...";
// Another request could claim here!
$sql = "UPDATE server_info SET user_id = ...";
```

**After:**
```php
// Atomic operation
$sql = "UPDATE server_info SET user_id = ? WHERE ... AND user_id IS NULL";
return $stmt->rowCount() > 0; // True only if we claimed it
```

### 10. Configuration Security ✅

**Before:**
```ini
; mysql.ini in web-accessible directory
host = localhost
password = plaintext_password
```

**After:**
```bash
# .env outside web root, gitignored
DB_HOST=localhost
DB_PASS=secure_password

# .env file permissions
chmod 600 .env
```

## Security Headers

Implemented via `.htaccess`:

```apache
# Prevent MIME sniffing
Header set X-Content-Type-Options "nosniff"

# Clickjacking protection
Header set X-Frame-Options "SAMEORIGIN"

# XSS Protection (older browsers)
Header set X-XSS-Protection "1; mode=block"

# Referrer Policy
Header set Referrer-Policy "strict-origin-when-cross-origin"

# Content Security Policy
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
```

## Attack Surface Reduction

### Removed/Deprecated

- Short PHP tags (`<?` → `<?php`)
- Global error display in production
- Direct database credentials in code
- Inline SQL queries
- Direct `$_GET`/`$_POST` usage without validation

### Added

- PDO prepared statements everywhere
- Input validation service
- Output escaping everywhere
- CSRF protection middleware
- Rate limiting middleware
- Secure session management
- Error logging instead of display

## Testing Security

### SQL Injection Tests

```bash
# Try common injection patterns
curl -X POST https://your-site.com/account/login \
  -d "username=admin' OR '1'='1" \
  -d "password=anything"
# Should fail gracefully
```

### XSS Tests

```bash
# Try injecting script
curl -X POST https://your-site.com/add \
  -d "server=<script>alert(1)</script>:21025"
# Should be escaped in output
```

### CSRF Tests

```bash
# Try POST without token
curl -X POST https://your-site.com/account/create \
  -d "username=test" \
  -d "email=test@test.com"
# Should fail with "CSRF token validation failed"
```

### Rate Limiting Tests

```bash
# Try multiple failed logins
for i in {1..10}; do
  curl -X POST https://your-site.com/account/login \
    -d "username=admin" \
    -d "password=wrong"
done
# Should block after 5 attempts
```

## Reporting Security Issues

If you discover a security vulnerability, please email: **screeps@fordo.sk**

**Do not** open a public GitHub issue for security vulnerabilities.

## Security Checklist

Before going live, verify:

- [ ] `.env` file is outside web root
- [ ] `.env` has 600 permissions
- [ ] `APP_DEBUG=false` in production
- [ ] `SESSION_SECURE=true` if using HTTPS
- [ ] HTTPS is enforced (uncomment in `.htaccess`)
- [ ] Web server doesn't expose `.env`, `.git`, etc.
- [ ] Database user has minimal privileges
- [ ] File permissions are correct (775 for storage/, 755 for code)
- [ ] Error logging is configured
- [ ] Backups are set up
- [ ] Monitoring is in place

## Future Improvements

Potential enhancements for even better security:

1. **Two-Factor Authentication** - Add 2FA for user accounts
2. **Email Verification** - Verify email addresses on registration
3. **API Rate Limiting** - Per-IP rate limiting for all endpoints
4. **Security Headers** - Add Strict-Transport-Security
5. **Database Encryption** - Encrypt sensitive data at rest
6. **Audit Logging** - Log all security-relevant events
7. **Intrusion Detection** - Monitor for suspicious patterns
8. **Password Complexity Meter** - Real-time feedback on password strength

## Compliance

This implementation follows:

- **OWASP Top 10** - Mitigates all major web vulnerabilities
- **PHP Security Best Practices** - Uses latest secure functions
- **PDO Best Practices** - Prepared statements everywhere
- **Session Security** - Follows PHP session hardening guide

## References

- OWASP Cheat Sheet Series: https://cheatsheetseries.owasp.org/
- PHP Security Guide: https://www.php.net/manual/en/security.php
- PDO Documentation: https://www.php.net/manual/en/book.pdo.php
- Password Hashing: https://www.php.net/manual/en/function.password-hash.php
