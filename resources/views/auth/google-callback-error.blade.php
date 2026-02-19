<!DOCTYPE html>
<html>
<head><title>Login failed</title></head>
<body>
<script>
    if (window.opener) {
        window.opener.postMessage({ type: 'google-auth-error' }, window.location.origin);
        window.close();
    } else {
        window.location.href = '/login';
    }
</script>
</body>
</html>
