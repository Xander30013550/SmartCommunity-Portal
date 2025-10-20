<!--    
    This script runs immediately in the page’s head to check if the sidebar was previously collapsed by 
    reading a value from localStorage, and if so, it adds a CSS class to the root `<html>` element to apply 
    the collapsed sidebar styling right from the start—ensuring the UI state is consistent before the 
    page fully loads.
-->
<head>
    <script>
        //  This immediately-invoked function checks if the sidebar was saved as collapsed in localStorage,
        //  and if so, adds a `sidebar-collapsed` class to the `<html>` element to apply the collapsed sidebar
        //  styling as early as possible.
        (function() {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (collapsed) {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Smart Community Portal</title>
    <link rel="stylesheet" href="./styles/annoucementBar.css" />
    <link rel="stylesheet" href="./styles/styles.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>