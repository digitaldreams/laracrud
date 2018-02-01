<div class="container">
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="https://bulma.io">
                <img src="https://bulma.io/images/bulma-logo.png"
                     alt="Bulma: a modern CSS framework based on Flexbox" width="112" height="28">
            </a>

            <button class="button navbar-burger" data-target="navMenu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="navbar-menu" id="navMenu">
            <div class="navbar-start">
                @if(auth()->check())
                    <a class="nav-item button is-primary" href="#">
                        <span class="icon">
                            <i class="fa fa-home"></i>
                        </span>
                        &nbsp;Home
                    </a>
                @section('navbar')
                    &nbsp;
                    @if(isset($enableSearch) && $enableSearch==true)
                        <div class="nav-item">
                            <div class="field has-addons">
                                <div class="control">
                                    <input class="input" type="text" placeholder="Find a repository">
                                </div>
                                <div class="control">
                                    <a class="button is-info">
                                        <span class="icon">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @endif
            </div>

            <div class="navbar-end">
                @if(auth()->guest())
                    <a href="{{route('login')}}" class="navbar-item button is-primary">
                        <i class="fa fa-sign-in"></i> &nbsp;Login
                    </a>&nbsp;
                    <a href="{{route('register')}}" class="navbar-item button is-primary">
                        <i class="fa fa-user-plus"></i> &nbsp;Register
                    </a>
                @else
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="#">
                                <span class="icon">
                                    <i class="fa fa-user"></i>
                                </span>
                            Your Name
                        </a>
                        <div class="navbar-dropdown is-boxed">

                            <a class="navbar-item" href="">
                                   <span class="icon">
                                       <i class="fa fa-wrench"></i>
                                   </span> Profile
                            </a>

                            <hr class="navbar-divider">
                            <a class="navbar-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                 <span class="icon">
                                     <i class="fa fa-sign-out"></i>
                                 </span> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                  style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>
</div>