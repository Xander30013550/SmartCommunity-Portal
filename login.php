<!DOCTYPE html>

<!--
    // -----------------------------------------------------------------------------
    //  Copyright (c) 2025 SMT Students
    //  All rights reserved.
    // 
    //  This file is part of Smart Community Portal (SCP).
    //  Unauthorized copying of this file, via any medium is strictly prohibited.
    //  Proprietary and confidential.
    // 
    //  Written by Michael McKIE, 2025
    // -----------------------------------------------------------------------------
-->

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title> Smart Community Portal </title>
        <link rel="stylesheet" href="./styles/styles.css" />
        <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    </head>

    <!--    Main Section    -->
    <body class="sb-expanded">
        <!--    Navigation Section      -->
        <nav id="sidebar">
            <ul>
                <!--    Collapse/Expand   -->
                <li>
                    <button onclick=toggleSidebar() id="toggle-btn">
                        <i id="icon-expand" class="bx bx-chevrons-right hidden"> </i>
                        <i id="icon-collapse" class="bx bx-chevrons-left"> </i>
                    </button>
                </li>

                <!--    Home    -->
                <li class="active">
                    <a href="./index.html">
                        <i class="bx bx-home-circle"> </i>
                        <span> Home </span>
                    </a>
                </li>

                <!--    Login    -->
                <li>
                    <a href="./login.html">
                        <i class="bx bx-user"> </i>
                        <span> Login </span>
                    </a>
                </li>

                <!--    Feedback    -->
                <li>
                    <a href="./feedback.html">
                        <i class='bx  bx-chat'  ></i> 
                        <span> Feedback </span>
                    </a>
                </li>

                <!--    Bookings    -->
                <li>
                    <a href="./bookings.html">
                        <i class="bx bx-book-open"> </i>
                        <span> Bookings </span>
                    </a>
                </li>

                <!--    About   -->
                <li>
                    <a href="./about.html">
                        <i class="bx bx-info-square"> </i>
                        <span> About </span>
                    </a>
                </li>
            </ul>
        </nav> <!--     End Navigation Section      -->
        
        <!--    Page Content    -->
        <main>
            <section>
                <!--    Login Form  -->
                <form id="login-form">
                    <!--    Company logo    -->
                    <img src="./images/CityLinkIcon.png" width="33%" style="margin: auto;" />
                    
                    <!--    Section header  -->
                    <h2> Sign in </h2>

                    <!--    Username section    -->
                    <label for="username"> Username or email </label>
                    <input type="text" id="username" name="username" required>

                    <!--    Password section    -->
                    <label for="password"> Password </label>
                    <input type="password" id="password" name="password" required>

                    <!--    Submission Button   -->
                    <button type="submit"> Submit </button>

                    <a href="#"> Forgot Password? </a>

                    <p> Don't have an account? </p>
                    <p>Register <a href="#"> Here</a>. </p>
                </form>
            </section>
        </main> <!--    End page content    -->

        <!--    Footer section      -->
        <Footer>
            &copy; 2025 CityLink Initiatives. &nbsp;
            <a href="privacy.html"> Privacy Policy </a>
        </Footer>

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>