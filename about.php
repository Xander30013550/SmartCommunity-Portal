<?php
//  This script initializes the menu system by loading dependencies, creating a 
//  MenuRepository and NavRenderer instance with config data, and sets the 
//  current page from the request URI for navigation purposes.

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
use App\Menu\MenuRepository;
use App\Menu\NavRenderer;
$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav      = new NavRenderer($menuRepo);
$current = $_SERVER['REQUEST_URI'] ?? '/index.php';
?>

<!DOCTYPE html>
<html lang="en">
    <?php include './shared/header.php'; ?>

<!--    Main Section    -->

<body class="sb-expanded">
    <?= $nav->render($current) ?>

    <!--    Page Content    -->
        
    <main>
        <img src="./images/CityLinkLogo.png" alt="CityLink Initiatives" class="logo"/><br>

        <div class="section">
            <h2>Our Mission</h2>
            <p>
                Our mission is to connect people, services, and opportunities in our community. By leveraging modern technology, we aim to streamline the delivery of public services and ensure that all residents have easy access to the resources they need. Through our work, we create an inclusive, accessible, and well-informed community that can thrive in the digital age.
            </p>
        </div>

        <div class="section">
            <h2>Our Vision</h2>
            <p>
                To be the leading local government agency in utilizing technology to transform how we engage with our community. We believe in a future where our residents can easily access information, communicate with local services, and participate in community-driven initiatives — all from the comfort of their homes.
            </p>
        </div>

        <div class="section">
            <h2>What We Do</h2>
            <p>
                CityLink Initiatives delivers a wide range of programs and services to our community, including:
            </p>
            <ul>
                <li>Event Bookings: From local festivals to community health days, we help residents stay informed and connected with what’s happening in the city.</li>
                <li>Waste Management: We offer waste collection scheduling, recycling initiatives, and educational resources to promote sustainability.</li>
                <li>Rates and Enquiries: We provide easy access to property rates, payment services, and inquiries for residents and businesses.</li>
                <li>Community Development: Our programs support local youth, empower volunteers, and promote public safety and well-being.</li>
            </ul>
        </div>

        <div class="section">
            <h2>Our Commitment to Digital Transformation</h2>
            <p>
                In line with our commitment to serve the community better, we are undergoing a <strong>digital transformation</strong> to modernize our services. The <strong>Smart Community Portal (SCP)</strong> is at the heart of this effort. By moving to an online platform, we aim to:
            </p>
            <ul>
                <li>Enhance access to local services, programs, and events.</li>
                <li>Provide a seamless and mobile-friendly experience.</li>
                <li>Foster stronger community engagement through transparent communication and feedback mechanisms.</li>
                <li>Ensure accessibility for all residents, including those with disabilities, and meet modern standards for privacy and data security.</li>
            </ul>
        </div>

        <div class="section">
            <h2>Why SCP?</h2>
            <p>
                The Smart Community Portal (SCP) is designed to be more than just a website. It’s a <strong>one-stop digital hub</strong> where residents can:
            </p>
            <ul>
                <li><strong>Book services</strong> (like waste collection, community health checks, and more).</li>
                <li><strong>View up-to-date announcements</strong> about local events and news.</li>
                <li><strong>Provide feedback</strong> on various services and engage with the community.</li>
                <li><strong>Create and manage user profiles</strong> to access tailored services.</li>
            </ul>
            <p>
                With the SCP, we aim to build a more connected, efficient, and responsive community.
            </p>
        </div>

        <div class="section">
            <h2>Our Core Values</h2>
            <ul>
                <li><strong>Community Engagement</strong>: We believe in actively involving our residents in shaping decisions and policies that affect them.</li>
                <li><strong>Transparency</strong>: Open and clear communication is essential to building trust. We are committed to providing real-time updates and information to our residents.</li>
                <li><strong>Innovation</strong>: Technology plays a key role in modernizing how we interact with the community. We embrace innovation in everything we do to drive efficiency and improve service delivery.</li>
                <li><strong>Accessibility</strong>: We are committed to ensuring that all our services are accessible to everyone, including people with disabilities, seniors, and those in underserved areas.</li>
            </ul>
        </div>

        <!--    Contact Details      -->
        <div class="section contact-info">
            <h2>Contact Us</h2>
            <p><strong>Email:</strong> contact@citylink.gov.au</p>
            <p><strong>Phone:</strong> [Phone Number]</p>
            <p><strong>Office Address:</strong> [Office Location]</p>
            <p><strong>Social Media:</strong> [Links to Social Media Accounts]</p>
        </div>
    </main> <!--    End page content    -->

        <!--    Footer section      -->
        <Footer>
            &copy; 2025 CityLink Initiatives. All rights reserved. &nbsp;
            <a href="privacy.php"> Privacy Policy </a>
        </Footer>

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>