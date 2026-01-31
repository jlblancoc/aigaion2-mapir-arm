<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
 * JAFMA 2025: Author Tabbed View
 * ===============================
 * This view displays publications for an author in a modern, tabbed interface.
 * 
 * Required variables from the controller:
 * - $header: Page title/header text
 * - $author_name: Author's display name
 * - $pubs_by_type: array of publications sorted by type
 * - $pubs_by_year: array of publications sorted by year
 * - $format, $style, $withlinks, $hide_bullets, $maxyearsfromnow
 * - $cssfile: Optional CSS file for additional styling
 *
 * URL Format:
 * https://ingmec.ual.es/aigaion2/index.php/export/byauthor_tabbed/{author_id}/{withlinks}/{cssfile}/{filtertype}/{list_type}/{maxyearsfromnow}
 */

// Ensure defaults for variables
if (!isset($withlinks)) $withlinks = '1';
if (!isset($hide_bullets)) $hide_bullets = true;
if (!isset($maxyearsfromnow) || $maxyearsfromnow == 'none') $maxyearsfromnow = 100;
if (!isset($cssfile)) $cssfile = '';

$pathaigaion = AIGAION_ROOT_URL;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($header); ?></title>
    
    <?php if (!empty($cssfile)): ?>
    <!-- Custom CSS file -->
    <link rel="stylesheet" href="<?php echo $pathaigaion; ?>/export_css/<?php echo $cssfile; ?>" type="text/css">
    <?php endif; ?>
    
    <style>
        /* ============================================
         * Modern Tabbed Publication List Styles
         * ============================================ */
        
        /* Reset and base styles */
        .aigaion-tabbed-container {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            max-width: 1100px;
            margin: 0 auto;
            padding: 1rem;
            background-color: #fafafa;
        }
        
        /* Header */
        .aigaion-tabbed-header {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        /* Tab Navigation */
        .aigaion-tab-nav {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        
        .aigaion-tab-btn {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: -2px;
        }
        
        .aigaion-tab-btn:hover {
            color: #f97316;
        }
        
        .aigaion-tab-btn.active {
            color: #f97316;
            border-bottom-color: #f97316;
            font-weight: 600;
        }
        
        /* Tab Content */
        .aigaion-tab-content {
            display: none;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
        }
        
        .aigaion-tab-content.active {
            display: block;
        }
        
        .aigaion-tab-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        /* Publication list styles */
        .aigaion-tab-content .aigaion_publist {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .aigaion-tab-content .aigaion_nobulletline,
        .aigaion-tab-content .aigaion_nobulletpubline,
        .aigaion-tab-content .aigaion_publine {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        /* Type headers */
        .aigaion-tab-content .aigaion_divtype {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        
        .aigaion-tab-content .aigaion_divtype:first-child {
            margin-top: 0;
        }
        
        .aigaion-tab-content .aigaion_divtype h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e40af;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0;
            border-left: 4px solid #3b82f6;
        }
        
        /* Year headers */
        .aigaion-tab-content .aigaion_divyear {
            font-size: 1rem;
            font-weight: 600;
            color: #059669;
            background: #ecfdf5;
            padding: 0.4rem 0.75rem;
            border-radius: 4px;
            margin: 1rem 0 0.5rem 0;
            display: inline-block;
            border-left: 3px solid #10b981;
        }
        
        /* Publication entry */
        .aigaion-tab-content .aigaion_publication {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            margin: 0.5rem 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: box-shadow 0.2s ease;
        }
        
        .aigaion-tab-content .aigaion_publication:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        /* Publication image */
        .aigaion-tab-content .aigaion_publication_image {
            flex-shrink: 0;
            width: 100px;
        }
        
        .aigaion-tab-content .aigaion_publication_image img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        
        /* Publication info */
        .aigaion-tab-content .aigaion_publication_info {
            flex: 1;
            min-width: 0;
        }
        
        .aigaion-tab-content .aigaion_publication_info .authors {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0 0 0.25rem 0;
        }
        
        .aigaion-tab-content .aigaion_publication_info .title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.25rem 0;
            line-height: 1.4;
        }
        
        .aigaion-tab-content .aigaion_publication_info .title a {
            color: #1d4ed8;
            text-decoration: none;
        }
        
        .aigaion-tab-content .aigaion_publication_info .title a:hover {
            text-decoration: underline;
        }
        
        .aigaion-tab-content .aigaion_publication_info .journal {
            font-size: 0.85rem;
            color: #4b5563;
            font-style: italic;
            margin: 0 0 0.5rem 0;
        }
        
        /* Links (PDF, BibTeX, etc.) */
        .aigaion-tab-content .aigaion_enlace {
            display: inline-block;
            margin-right: 0.5rem;
        }
        
        .aigaion-tab-content .aigaion_enlace img {
            height: 16px;
            width: auto;
            vertical-align: middle;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .aigaion-tab-content .aigaion_enlace:hover img {
            opacity: 1;
        }
        
        /* Empty publication entries (spacers) */
        .aigaion-tab-content .aigaion_publication:empty {
            display: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .aigaion-tabbed-container {
                padding: 0.5rem;
            }
            
            .aigaion-tabbed-header {
                font-size: 1.25rem;
            }
            
            .aigaion-tab-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .aigaion-tab-content .aigaion_publication {
                flex-direction: column;
            }
            
            .aigaion-tab-content .aigaion_publication_image {
                width: 80px;
            }
        }
    </style>
</head>
<body>

<div class="aigaion-tabbed-container">
    
    <!-- <h1 class="aigaion-tabbed-header">
        <?php echo htmlspecialchars($header); ?>
    </h1> -->

    <!-- Tab Navigation Buttons -->
    <nav class="aigaion-tab-nav">
        <button 
            id="tab-btn-type" 
            class="aigaion-tab-btn active" 
            data-tab="tab-content-type"
            type="button">
            📚 By Type
        </button>
        <button 
            id="tab-btn-year" 
            class="aigaion-tab-btn" 
            data-tab="tab-content-year"
            type="button">
            📅 By Year
        </button>
    </nav>

    <!-- Tab Content: Publications by Type -->
    <div id="tab-content-type" class="aigaion-tab-content active">
        <h3 class="aigaion-tab-title">Publications Sorted by Type</h3>
        <?php 
            // Prepare data for the embedded list view
            $inner_view_data_type = array(
                'nonxrefs'       => $pubs_by_type,
                'xrefs'          => array(),
                'withlinks'      => $withlinks,
                'hide_bullets'   => $hide_bullets,
                'sort'           => 'type',
                'maxyearsfromnow'=> $maxyearsfromnow
            );
            // Load the embeddable inner view
            $this->load->view('export/mapir_formatted_image_list_embed', $inner_view_data_type, False); 
        ?>
    </div>
    
    <!-- Tab Content: Publications by Year -->
    <div id="tab-content-year" class="aigaion-tab-content">
        <h3 class="aigaion-tab-title">Publications Sorted by Year</h3>
        <?php 
            // Prepare data for the embedded list view
            $inner_view_data_year = array(
                'nonxrefs'       => $pubs_by_year,
                'xrefs'          => array(),
                'withlinks'      => $withlinks,
                'hide_bullets'   => $hide_bullets,
                'sort'           => 'year',
                'maxyearsfromnow'=> $maxyearsfromnow
            );
            // Load the embeddable inner view
            $this->load->view('export/mapir_formatted_image_list_embed', $inner_view_data_year, False); 
        ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.aigaion-tab-btn');
    const contents = document.querySelectorAll('.aigaion-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab');
            
            // Remove 'active' class from all buttons and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // Add 'active' class to clicked button
            this.classList.add('active');

            // Show target content
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});
</script>

</body>
</html>
