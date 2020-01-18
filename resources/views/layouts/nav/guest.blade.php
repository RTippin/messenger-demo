<ul class="navbar-nav mr-auto">

</ul>
<ul class="navbar-nav">
	<li class="nav-item {{Request::is('register') ? 'active' : ''}}">
		<a class="nav-link" href="{{ url('/register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
	</li>
	<li class="nav-item {{Request::is('login') ? 'active' : ''}}">
		<a class="nav-link" href="{{ url('/login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
	</li>
</ul>
