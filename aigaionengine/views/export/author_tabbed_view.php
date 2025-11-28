<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
* JAFMA 2025: Author Tabbed View
* This view displays publications for an author in a modern, tabbed interface.
* It requires the following variables from the controller:
* - $header
* - $author_name
* - $pubs_by_type (array of publications sorted by type)
* - $pubs_by_year (array of publications sorted by year)
* - $list_type (The name of the inner view/list formatter, e.g., 'mapir_formatted_list')
* - $format, $style, $withlinks, $hide_bullets (passed to inner view)
*
* https://ingmec.ual.es/aigaion2/index.php/export/byauthor_tabbed/{author_id}/{withlinks}/{cssfile}/{filtertype}/{list_type}/{maxyearsfromnow}
*
*/

// Define a common structure for passing variables to the inner list views
$inner_view_data = array(
    'format'       => $format,
    'style'        => $style,
    'withlinks'    => $withlinks,
    'hide_bullets' => $hide_bullets,
    'xrefs'        => array() // Assuming xrefs are already resolved/merged in the controller data
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $header; ?></title>
    <!-- Load Tailwind CSS from CDN for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for the tabs */
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-btn.active {
            border-bottom: 3px solid #f97316; /* Tailwind orange-500 */
            color: #f97316;
            font-weight: 600;
        }
        /* Custom CSS to inject the list-style changes, as inner views load via PHP include */
        .aigaion_publication_list_container ul,
        .aigaion_publication_list_container ol {
            /* Remove default padding for list containers */
            padding-left: 0;
        }
        /* Apply list-style removal for the no-bullet class used in the inner view */
        .aigaion_publication_list_container .aigaion_nobulletpubline {
            list-style-type: none !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        /* Style adjustments for the main container */
        .aigaion-container {
            max-width: 1000px;
            margin: auto;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <div class="aigaion-container">
        
        <h1 class="text-3xl font-extrabold text-gray-800 border-b pb-2 mb-6">
            <?php echo $header; ?>
        </h1>

        <!-- Tab Navigation Buttons -->
        <div class="flex border-b border-gray-200 space-x-4 mb-6">
            <button 
                id="tab-btn-type" 
                class="tab-btn active px-4 py-2 text-lg text-gray-600 transition duration-300 hover:text-orange-500" 
                data-tab="tab-content-type">
                Publications by Type
            </button>
            <button 
                id="tab-btn-year" 
                class="tab-btn px-4 py-2 text-lg text-gray-600 transition duration-300 hover:text-orange-500" 
                data-tab="tab-content-year">
                Publications by Year
            </button>
        </div>

        <!-- Tab Content Containers -->
        <div class="aigaion_publication_list_container">

            <!-- Content for 'Publications by Type' Tab -->
            <div id="tab-content-type" class="tab-content active p-4 bg-white shadow-lg rounded-lg">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">Sorted by Type</h3>
                <?php 
                    // Set the publications for this specific tab content
                    $inner_view_data['nonxrefs'] = $pubs_by_type;
                    // Load the inner view template (e.g., 'mapir_formatted_image_list')
                    $this->load->view('export/'.$list_type, $inner_view_data, False); 
                ?>
            </div>
            
            <!-- Content for 'Publications by Year' Tab -->
            <div id="tab-content-year" class="tab-content p-4 bg-white shadow-lg rounded-lg">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">Sorted by Year</h3>
                <?php 
                    // Set the publications for this specific tab content
                    $inner_view_data['nonxrefs'] = $pubs_by_year;
                    // Load the inner view template (e.g., 'mapir_formatted_image_list')
                    $this->load->view('export/'.$list_type, $inner_view_data, False); 
                ?>
            </div>

        </div> <!-- /.aigaion_publication_list_container -->

    </div> <!-- /.aigaion-container -->


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-tab');
                    
                    // Remove 'active' class from all buttons and hide all contents
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

                    // Add 'active' class to the clicked button
                    this.classList.add('active');

                    // Show the target content
                    const targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });

            // Ensure one tab is active on load (handled by initial classes, but good practice)
            if (!document.querySelector('.tab-btn.active')) {
                const defaultTab = document.getElementById('tab-btn-type');
                const defaultContent = document.getElementById('tab-content-type');
                if (defaultTab) defaultTab.classList.add('active');
                if (defaultContent) defaultContent.classList.add('active');
            }
        });
    </script>

</body>
</html>