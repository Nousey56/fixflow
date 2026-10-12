<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FixFlow | Maintenance</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "SF Pro Text",
                "Helvetica Neue",
                Arial,
                sans-serif;

            background: #ffffff;
            color: #1d1d1f;

            -webkit-font-smoothing: antialiased;
        }


        /* =========================
           NAVIGATION
        ========================= */

        .navbar {
            height: 52px;

            background: rgba(255, 255, 255, 0.82);

            border-bottom: 1px solid rgba(0, 0, 0, 0.06);

            position: sticky;
            top: 0;
            z-index: 100;

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .nav-container {
            max-width: 1100px;
            height: 100%;

            margin: 0 auto;
            padding: 0 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            color: #1d1d1f;

            font-size: 19px;
            font-weight: 600;

            letter-spacing: -0.4px;

            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            color: #424245;

            font-size: 13px;

            text-decoration: none;

            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #000000;
        }


        /* =========================
           BUTTON
        ========================= */

        .new-request {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            background: #0071e3;
            color: #ffffff;

            padding: 8px 15px;

            border-radius: 980px;

            font-size: 13px;
            font-weight: 500;

            text-decoration: none;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .new-request:hover {
            background: #0077ed;
        }

        .new-request:active {
            transform: scale(0.97);
        }


        /* =========================
           HERO
        ========================= */

        .hero {
            max-width: 1000px;

            margin: 0 auto;

            padding: 110px 24px 80px;

            text-align: center;
        }

        .hero-label {
            margin-bottom: 16px;

            color: #6e6e73;

            font-size: 17px;
            font-weight: 600;
        }

        .hero h1 {
            max-width: 800px;

            margin: 0 auto;

            font-size: clamp(48px, 7vw, 78px);

            line-height: 0.98;

            letter-spacing: -4px;

            font-weight: 700;
        }

        .hero-description {
            max-width: 650px;

            margin: 28px auto 0;

            color: #6e6e73;

            font-size: 21px;

            line-height: 1.45;

            letter-spacing: -0.3px;
        }


        /* =========================
           DASHBOARD
        ========================= */

        .dashboard {
            background: #f5f5f7;

            padding: 80px 24px 110px;
        }

        .dashboard-container {
            max-width: 1000px;

            margin: 0 auto;
        }

        .section-heading {
            display: flex;

            align-items: flex-end;
            justify-content: space-between;

            margin-bottom: 28px;
        }

        .section-heading h2 {
            margin: 0;

            font-size: 38px;

            letter-spacing: -1.5px;
        }

        .section-heading p {
            margin: 8px 0 0;

            color: #6e6e73;

            font-size: 16px;
        }


        /* =========================
           REQUEST CARDS
        ========================= */

        .requests {
            display: grid;

            gap: 18px;
        }

        .request-card {
            background: #ffffff;

            border-radius: 24px;

            padding: 30px;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .request-card:hover {
            transform: translateY(-2px);

            box-shadow:
                0 12px 35px rgba(0, 0, 0, 0.06);
        }

        .request-top {
            display: flex;

            align-items: center;
            justify-content: space-between;

            margin-bottom: 18px;
        }


        /* =========================
           PRIORITY
        ========================= */

        .priority {
            display: inline-flex;

            padding: 6px 10px;

            border-radius: 980px;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 0.5px;

            text-transform: uppercase;
        }

        .priority-low {
            background: #e8f5e9;
            color: #248a3d;
        }

        .priority-medium {
            background: #fff4d6;
            color: #9a6700;
        }

        .priority-high {
            background: #ffe5e5;
            color: #b42318;
        }

        .priority-emergency {
            background: #ff3b30;
            color: #ffffff;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            color: #6e6e73;

            font-size: 13px;

            font-weight: 500;

            text-transform: capitalize;
        }


        /* =========================
           REQUEST INFORMATION
        ========================= */

        .request-card h3 {
            margin: 0;

            font-size: 27px;

            line-height: 1.15;

            letter-spacing: -0.8px;
        }

        .location {
            margin: 10px 0 0;

            color: #6e6e73;

            font-size: 15px;
        }

        .floor {
            margin-left: 6px;
        }

        .description {
            max-width: 700px;

            margin: 22px 0 0;

            color: #424245;

            font-size: 16px;

            line-height: 1.55;
        }


        /* =========================
           REQUEST FOOTER
        ========================= */

        .request-footer {
            display: flex;

            align-items: center;
            justify-content: space-between;

            margin-top: 28px;

            padding-top: 20px;

            border-top: 1px solid #e8e8ed;
        }

        .view-request {
            color: #0066cc;

            font-size: 14px;

            font-weight: 500;

            text-decoration: none;
        }

        .view-request:hover {
            text-decoration: underline;
        }

        .arrow {
            margin-left: 3px;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {
            background: #ffffff;

            padding: 60px 30px;

            border-radius: 24px;

            text-align: center;
        }

        .empty-state h3 {
            margin: 0 0 8px;

            font-size: 24px;
        }

        .empty-state p {
            margin: 0;

            color: #6e6e73;
        }


        /* =========================
           SUCCESS MESSAGE
        ========================= */

        .success-message {
            margin-bottom: 25px;

            padding: 16px 20px;

            background: #e8f5e9;

            color: #248a3d;

            border-radius: 14px;

            font-size: 14px;

            font-weight: 500;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #f5f5f7;

            padding: 25px 24px;

            border-top: 1px solid #d2d2d7;
        }

        .footer-container {
            max-width: 1000px;

            margin: 0 auto;

            color: #6e6e73;

            font-size: 12px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 700px) {

            .navbar { height: auto; }
            .nav-container { flex-wrap: wrap; gap: 12px; padding-top: 12px; padding-bottom: 12px; }
            .nav-links { display: flex; flex-wrap: wrap; gap: 14px; order: 3; width: 100%; }
            .nav-links a { font-size: 12px; }

            .hero {
                padding-top: 80px;

                padding-bottom: 60px;
            }

            .hero h1 {
                font-size: 48px;

                letter-spacing: -2.5px;
            }

            .hero-description {
                font-size: 18px;
            }

            .dashboard {
                padding-top: 60px;
            }

            .section-heading {
                align-items: flex-start;

                flex-direction: column;

                gap: 18px;
            }

            .section-heading h2 {
                font-size: 32px;
            }

            .request-card {
                padding: 24px;

                border-radius: 20px;
            }

            .request-card h3 {
                font-size: 23px;
            }
        }

    </style>
</head>


<body>

    @php
        $membershipRoles = \App\Models\CompanyMembership::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('role');

        $canManageProperties = $membershipRoles->contains('company_admin') || $membershipRoles->contains('manager');
        $isCompanyAdmin = $membershipRoles->contains('company_admin');
        $isTechnician = $membershipRoles->contains('technician');
    @endphp




    <!-- =========================
         NAVIGATION
    ========================== -->

    <x-fixflow-nav />



    <!-- =========================
         HERO
    ========================== -->

    <section class="hero">

        <div class="hero-label">
            FixFlow
        </div>


        <h1>
            Maintenance.<br>
            Without the mess.
        </h1>


        <p class="hero-description">

            Manage properties, track maintenance requests,
            and keep every repair moving from one simple place.

        </p>

    </section>



    <!-- =========================
         MAINTENANCE DASHBOARD
    ========================== -->

    <section
        class="dashboard"
        id="requests"
    >

        <div class="dashboard-container">


            <!-- SUCCESS MESSAGE -->

            @if (session('success'))

                <div class="success-message">

                    {{ session('success') }}

                </div>

            @endif



            <!-- SECTION HEADING -->

            <div class="section-heading">

                <div>

                    <h2>
                        Recent requests.
                    </h2>

                    <p>
                        Everything that needs your attention.
                    </p>

                </div>


                @if ($canManageProperties)
                    <a href="{{ route('maintenance.create') }}" class="new-request">+ New Request</a>
                @endif

            </div>



            <!-- =========================
                 REQUEST LIST
            ========================== -->

            <div class="requests">


                @forelse ($requests as $request)


                    <article class="request-card">


                        <!-- TOP -->

                        <div class="request-top">


                            <span
                                class="priority priority-{{ strtolower($request->priority) }}"
                            >

                                {{ $request->priority }}

                            </span>


                            <span class="status">

                                {{ str_replace('_', ' ', $request->status) }}

                            </span>


                        </div>



                        <!-- ISSUE -->

                        <h3>

                            {{ $request->title }}

                        </h3>



                        <!-- PROPERTY / UNIT / FLOOR -->

                        <p class="location">

                            {{ $request->unit->property->name }}

                            · Unit {{ $request->unit->unit_number }}

                            @if ($request->unit->floor)

                                <span class="floor">
                                    · Floor {{ $request->unit->floor }}
                                </span>

                            @endif

                        </p>



                        <!-- DESCRIPTION / COMMENTS -->

                        <p class="description">

                            {{ $request->description }}

                        </p>



                        <!-- FOOTER -->

                        <div class="request-footer">


                            <span class="status">

                                Status:

                                {{ str_replace('_', ' ', $request->status) }}

                            </span>


                            <a
    href="{{ route('maintenance.show', $request) }}"
    class="view-request"
>
    View request

    <span class="arrow">
        ›
    </span>
</a>


                        </div>


                    </article>


                @empty


                    <div class="empty-state">

                        <h3>
                            No maintenance requests.
                        </h3>

                        <p>
                            New requests will appear here.
                        </p>

                    </div>


                @endforelse


            </div>

        </div>

    </section>



    <!-- =========================
         FOOTER
    ========================== -->

    <footer>

        <div class="footer-container">

            FixFlow · Property maintenance management

        </div>

    </footer>


</body>

</html>