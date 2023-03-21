<script type="text/javascript">
    sessionStorage.setItem('token', '{{ $token }}');
    sessionStorage.setItem('userId', '{{ $id }}');
    sessionStorage.setItem('loginAs', 'true');

    localStorage.removeItem('userId');
    localStorage.removeItem('token');
    localStorage.removeItem('remember_login');

    window.location.href = '/';
</script>

<div style="margin: 150px; text-align: center;">
    <a href="/" style="text-decoration: none;"> ===> Logging in <=== </a>
</div>
