<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login - Psikologi</title>
        <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-white">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Login</h3>
                                    </div>
                                    <div class="card-body">
                                        @if (session('status'))
                                            <div class="alert alert-success">{{ session('status') }}</div>
                                        @endif

                                        <form action="{{ route('login') }}" method="POST">
                                            @csrf
                                            <div class="form-floating mb-3">
                                                <input
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    name="email"
                                                    id="email"
                                                    type="email"
                                                    placeholder="name@example.com"
                                                    value="{{ old('email') }}"
                                                    required
                                                />
                                                <label for="email">Email address</label>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password"
                                                    id="password"
                                                    type="password"
                                                    placeholder="Password"
                                                    required
                                                />
                                                <label for="password">Password</label>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-check mb-3">
                                                <input
                                                    class="form-check-input"
                                                    name="remember"
                                                    id="inputRememberPassword"
                                                    type="checkbox"
                                                    value="1"
                                                />
                                                <label class="form-check-label" for="inputRememberPassword">
                                                    Remember Me
                                                </label>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-center mt-4 mb-0">
                                                <button type="submit" class="btn btn-primary">Login</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                @include('layouts.partials.footer')
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="{{ asset('assets/js/scripts.js') }}"></script>
    </body>
</html>