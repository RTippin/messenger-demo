<footer class="container-fluid w-100 bg-dark text-light">
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="navbar-text">
			Tippin <i class="far fa-copyright"></i> 2020
		</div>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#footerDropUp" aria-controls="footerDropUp" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div id="footerDropUp" class="navbar-collapse collapse">
			<ul class="navbar-nav mr-auto"></ul>
			<ul class="navbar-nav">
				<li class="nav-item {{Request::is('Contact') ? 'active' : ''}}" >
					<a class="nav-link" href="{{route('contact_us')}}">Contact <i class="far fa-envelope"></i></a>
				</li>
			</ul>
		</div>
	</nav>
</footer>
