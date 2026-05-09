<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MediFind | Smart Pharmacy Inventory System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background:#f4f6fb;
            font-family:Arial, Helvetica, sans-serif;
        }

        .navbar{
            background:white;
            padding:20px 0;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

        .brand{
            font-size:42px;
            font-weight:700;
            color:#111827;
        }

        .hero{
            padding:80px 0;
        }

        .hero-title{
            font-size:72px;
            font-weight:500;
            line-height:1.05;
            color:#111827;
        }

        .hero-sub{
            font-size:20px;
            color:#5b6475;
            margin-top:25px;
            max-width:700px;
        }

        .search-card{
            background:white;
            border-radius:24px;
            padding:35px;
            box-shadow:0 10px 30px rgba(0,0,0,0.05);
        }

        .section-card{
            background:white;
            border-radius:24px;
            padding:35px;
            height:100%;
            box-shadow:0 10px 30px rgba(0,0,0,0.04);
        }

        .section-card h3{
            font-size:28px;
            font-weight:600;
            margin-bottom:20px;
        }

        .section-card p{
            color:#5b6475;
            font-size:18px;
            line-height:1.7;
        }

        .btn-main{
            width:100%;
            padding:14px;
            border-radius:14px;
            font-size:22px;
            font-weight:500;
        }

        .mini-btn{
            width:100%;
            border-radius:12px;
            padding:12px;
            font-size:20px;
        }

        .hero-tag{
            font-size:20px;
            font-weight:600;
            color:#4b5563;
            margin-bottom:20px;
        }

        .nav-link{
            font-size:22px;
            font-weight:500;
            color:#111827 !important;
            margin-left:20px;
        }

        .form-label{
            font-size:22px;
            font-weight:500;
        }

        .form-control{
            padding:14px;
            font-size:22px;
            border-radius:14px;
        }

        @media(max-width:991px){

            .hero-title{
                font-size:50px;
            }

            .search-card{
                margin-top:40px;
            }

            .nav-link{
                margin-left:0;
            }
        }

    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg">

        <div class="container">

            <a class="navbar-brand brand" href="#">
                MediFind
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navMenu">

                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Search</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">User Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Pharmacy Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Admin</a>
                    </li>

                </ul>

            </div>

        </div>

    </nav>


    <section class="hero">

        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-7">

                    <div class="hero-tag">
                        SMART PHARMACY INVENTORY SYSTEM
                    </div>

                    <h1 class="hero-title">
                        Find nearby medicine stock before visiting the shop.
                    </h1>

                    <p class="hero-sub">
                        MediFind connects customers with local pharmacies and keeps pharmacy inventory visible, searchable, and easier to manage.
                    </p>

                </div>


                <div class="col-lg-5">

                    <div class="search-card">

                        <form action="user/search_results.php" method="GET">

                            <div class="mb-4">

                                <label class="form-label">
                                    Medicine name
                                </label>

                                <input 
                                    type="text"
                                    name="q"
                                    class="form-control"
                                    placeholder="Search Dolo 650, Azithral 500..."
                                    list="medicineSuggestions"
                                    required
                                >

                                <datalist id="medicineSuggestions">

                                    <option value="Dolo 650">
                                    <option value="Crocin Advance">
                                    <option value="Paracetamol 500">
                                    <option value="Azithral 500">
                                    <option value="Azee 500">
                                    <option value="Amox 500">
                                    <option value="Cetzine">
                                    <option value="Levocet">
                                    <option value="Benadryl">
                                    <option value="Pantocid 40">
                                    <option value="Pan 40">
                                    <option value="Glycomet">
                                    <option value="Glyciphage">
                                    <option value="Telma 40">
                                    <option value="Atorva 10">
                                    <option value="Shelcal 500">
                                    <option value="Evion 400">

                                </datalist>

                            </div>


                            <div class="row">

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        City
                                    </label>

                                    <input 
                                        type="text"
                                        name="city"
                                        class="form-control"
                                        value="Bangalore"
                                        placeholder="Bangalore"
                                        list="citySuggestions"
                                        required
                                    >

                                    <datalist id="citySuggestions">

                                        <option value="Bangalore">

                                    </datalist>

                                </div>


                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Area
                                    </label>

                                    <input 
                                        type="text"
                                        name="area"
                                        class="form-control"
                                        value="Whitefield"
                                        placeholder="Whitefield"
                                        list="areaSuggestions"
                                        required
                                    >

                                    <datalist id="areaSuggestions">

                                        <option value="Whitefield">
                                        <option value="Koramangala">
                                        <option value="HSR Layout">
                                        <option value="Indiranagar">
                                        <option value="Bellandur">
                                        <option value="Marathahalli">
                                        <option value="Electronic City">
                                        <option value="JP Nagar">
                                        <option value="Banashankari">
                                        <option value="Yelahanka">

                                    </datalist>

                                </div>

                            </div>


                            <button type="submit" class="btn btn-primary btn-main">
                                Search Medicine
                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <div class="row mt-5 g-4">

                <div class="col-lg-4">

                    <div class="section-card">

                        <h3>For users</h3>

                        <p>
                            Search medicine availability, compare nearby pharmacy stock, check prices and place reservation orders instantly.
                        </p>

                        <button class="btn btn-outline-primary mini-btn mt-3">
                            Create user account
                        </button>

                    </div>

                </div>


                <div class="col-lg-4">

                    <div class="section-card">

                        <h3>For pharmacies</h3>

                        <p>
                            Maintain medicine inventory, update stock availability, manage pricing, expiry tracking and low-stock alerts.
                        </p>

                        <button class="btn btn-outline-primary mini-btn mt-3">
                            Pharmacy login
                        </button>

                    </div>

                </div>


                <div class="col-lg-4">

                    <div class="section-card">

                        <h3>For admin</h3>

                        <p>
                            Manage pharmacies, medicines, users, reports, inventory analytics and complete platform operations.
                        </p>

                        <button class="btn btn-outline-primary mini-btn mt-3">
                            Admin login
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>