<ul class="navbar-nav mr-auto">

</ul>
<ul class="navbar-nav">
	<li class="nav-item {{request()->route()->getName() === 'register' ? 'active' : ''}}">
		<a class="nav-link" href="{{ route('register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
	</li>
	<li class="nav-item {{request()->route()->getName() === 'login' ? 'active' : ''}}">
		<a class="nav-link" href="{{ route('login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
	</li>
</ul>
