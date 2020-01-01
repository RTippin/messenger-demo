<ul class="navbar-nav mr-auto">

</ul>
<ul class="navbar-nav">
	<li class="nav-item {{Request::is('register') ? 'active' : ''}}">
		<a class="nav-link" href="{{ url('/register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
	</li>
	<li class="nav-item {{Request::is('login') ? 'active' : ''}}">
		<a {!! Request::is('messenger/join/*') || Request::is('login') || Request::is('password/reset') || Request::is('register') ? '' : 'onclick="GuestManager.loginPopup(); return false;"' !!}
		   class="nav-link" href="{{ url('/login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
	</li>
</ul>
