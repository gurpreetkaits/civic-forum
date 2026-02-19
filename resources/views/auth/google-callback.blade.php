<!DOCTYPE html>
<html>
<head><title>Signing in...</title></head>
<body>
<script>
    if (window.opener) {
        window.opener.postMessage({ type: 'google-auth-success' }, window.location.origin);
        window.close();
    } else {
        window.location.href = '/';
    }
</script>
</body>
</html>
