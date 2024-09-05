<?php
/*
Plugin Name: WPMax Directory Listings Endorsements
Description: Adds endorsement categories to all WPMax Directory Listings on the front end.
Version: 1.8
Author: Sajid Khan
*/


// Enqueue jQuery if not already loaded
function endorsement_enqueue_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'endorsement_enqueue_scripts');

// Handle AJAX request
function update_endorsement() {
    if (!isset($_POST['category']) || !isset($_POST['listing_id']) || !isset($_POST['voteChange'])) {
        wp_send_json_error(['message' => 'Invalid data']);
    }

    $category = sanitize_text_field($_POST['category']);
    $listing_id = intval($_POST['listing_id']);
    $voteChange = intval($_POST['voteChange']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    error_log("Category: $category, Listing ID: $listing_id, Vote Change: $voteChange, IP: $ip_address");

    $user_votes = get_transient('endorsement_votes_' . $ip_address);
    if (!$user_votes) {
        $user_votes = [];
    }

    $user_vote_key = $listing_id . '_' . $category;
    $has_voted = in_array($user_vote_key, $user_votes);

    error_log("User Votes: " . print_r($user_votes, true));
    error_log("User Vote Key: $user_vote_key, Has Voted: " . ($has_voted ? 'Yes' : 'No'));

    if ($has_voted && $voteChange == 1) {
        wp_send_json_error(['message' => 'You have already voted for this category.']);
    } elseif (!$has_voted && $voteChange == -1) {
        wp_send_json_error(['message' => 'You have not voted for this category yet.']);
    }

    // Retrieve the current vote count from the database
    $current_votes = get_post_meta($listing_id, 'endorsement_' . $category, true);
    $current_votes = $current_votes ? intval($current_votes) : 0;

    error_log("Current Votes: $current_votes");

    // Update the vote count
    $new_votes = max(0, $current_votes + $voteChange);

    error_log("New Votes: $new_votes");

    // Save the new vote count back to the database
    update_post_meta($listing_id, 'endorsement_' . $category, $new_votes);

    // Update user meta
    if ($voteChange == 1) {
        $user_votes[] = $user_vote_key;
    } else {
        $user_votes = array_diff($user_votes, [$user_vote_key]);
    }
    set_transient('endorsement_votes_' . $ip_address, $user_votes, WEEK_IN_SECONDS);

    //error_log("Updated User Votes: " . print_r($user_votes, true));

    wp_send_json_success(['newVoteCount' => $new_votes]);
}

add_action('wp_ajax_update_endorsement', 'update_endorsement');
add_action('wp_ajax_nopriv_update_endorsement', 'update_endorsement');

// Function to output endorsement categories
function display_endorsement_categories($listing_id) {
    $categories = [
        'coaching' => 'Coaches with extensive knowledge and experience.',
        'facilities' => 'Well-maintained facilities.',
        'communication' => 'Clear, responsive, and timely communication between the club and its members.',
        'Value for Money' => 'Programs and services that provide excellent quality for the cost.'
    ];
    ?>
    <div id="categories-<?php echo $listing_id; ?>" class="endorsement-categories">
<!-- Include Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<h5>
    Endorsements 
    <span class="info-tooltip">
        <i class="fas fa-info-circle"></i>
        <span class="tooltiptext">Endorsements are a way to show support and appreciation for a volleyball club.</span>
    </span>
</h5>
        <p style="padding-left: 25px; margin-top: 0; margin-bottom: 15px;">Click the button to vote for each category for this club.</p>
        <?php foreach ($categories as $category => $tooltip): 
            $vote_count = get_post_meta($listing_id, 'endorsement_' . $category, true) ?: 0;
            $css_class = $vote_count > 0 ? 'category' : 'button-style-2';
        ?>
            <div class="<?php echo $css_class; ?>" data-category="<?php echo $category; ?>" data-listing-id="<?php echo $listing_id; ?>">
                <?php echo $vote_count > 0 ? ucfirst(str_replace('_', ' ', $category)) : '+ ' . ucfirst(str_replace('_', ' ', $category)); ?>
                <?php if ($vote_count > 0): ?>
                    <span class="vote-count"><?php echo $vote_count; ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <style>
.info-tooltip {
    margin-left: 5px; /* Reduced the margin to bring the icon closer to the text */
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
}

.info-tooltip .fa-info-circle {
    color: #ccc;
    font-size: 20px;
}

.info-tooltip .tooltiptext {
    visibility: hidden;
    width: 220px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px 0;
    position: absolute;
	font-size: 14px;
	font-weight: normal;
    z-index: 1;
    bottom: 125%; /* Position the tooltip above the info icon */
    left: 50%;
    margin-left: -110px; /* Center the tooltip */
    opacity: 0;
    transition: opacity 0.3s;
}

.info-tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}



		
		
        .category {
            background-color: #006cff;
            border: solid 1px #006cff;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            margin-left: 25px;
            margin-top: 20px;
            text-align: center;
            position: relative;
        }

        .button-style-2 {
            background-color: transparent;
            border: 1px dotted black;
            color: black;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            margin-left: 25px;
            margin-top: 20px;
            text-align: center;
            position: relative;
        }

        .button-style-2:hover {
            background-color: black;
            color: white;
        }

        .endorsement-categories {
            border-radius: 5px;
            margin-bottom: 25px;
            background: white;
            padding-bottom: 20px;
        }
        
        .voted {
            border-color: green;
        }

        .endorsement-categories h5 {
            padding-top: 25px;
            margin-top: 15px;
            padding-left: 25px;
        }

        .vote-count {
            background-color: #ffffff;
            color: #000;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }

        .category:hover, .button-style-2:hover {
            background-color: #f0f0f0;
            transition: background-color 0.2s;
        }

        .category.voted:hover {
            background-color: #d0f0d0;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            $('.endorsement-categories .category, .endorsement-categories .button-style-2').click(function() {
                var categoryElement = $(this);
                var category = categoryElement.data('category');
                var listingId = categoryElement.data('listing-id');
                var voted = categoryElement.hasClass('voted');
                var voteChange = voted ? -1 : 1;

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'update_endorsement',
                        category: category,
                        listing_id: listingId,
                        voteChange: voteChange
                    },
                    success: function(response) {
                        if (response.success) {
                            var newVoteCount = response.data.newVoteCount;
                            categoryElement.find('.vote-count').text(newVoteCount);
                            categoryElement.toggleClass('voted');

                            // Update CSS class and text based on new vote count
                            if (newVoteCount > 0) {
                                categoryElement.removeClass('button-style-2').addClass('category');
                                categoryElement.html(ucfirst(category) + ' <span class="vote-count">' + newVoteCount + '</span>');
                            } else {
                                categoryElement.removeClass('category').addClass('button-style-2');
                                categoryElement.text('+ ' + ucfirst(category));
                            }
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX request failed:', status, error);
                        alert('AJAX request failed.');
                    }
                });
            });

            function ucfirst(string) {
                return string.charAt(0).toUpperCase() + string.slice(1).replace('_', ' ');
            }
        });
    </script>
    <?php
}

// Hook the function to display endorsement categories into the content of directory listings
function add_endorsement_categories_to_directory_listings($content) {
    if (is_singular('directory_listing')) { // Replace 'directory_listing' with the actual post type for WPMax Directory Listings
        global $post;
        ob_start();
        display_endorsement_categories($post->ID);
        $endorsement_html = ob_get_clean();
        $content .= $endorsement_html;
    }
    return $content;
}



function wpmax_endorsements_add_admin_menu() {
    add_menu_page(
        'Endorsement Dashboard',
        'Endorsements',
        'manage_options',
        'wpmax-endorsements',
        'wpmax_endorsements_dashboard_page',
        'dashicons-thumbs-up',
        20
    );
}

function wpmax_endorsements_dashboard_page() {
    // Fetch all directory listings
    $listings = get_posts([
        'post_type' => 'at_biz_dir', // Ensure this matches the post type in use
        'posts_per_page' => -1,
    ]);

    // Define the endorsement categories
    $categories = [
        'coaching' => 'Coaching',
        'facilities' => 'Facilities',
        'communication' => 'Communication',
        'Value for Money' => 'Value for Money',
    ];
    ?>
    <div class="wrap">
        <h1>Endorsement Dashboard</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="manage-column column-title">Listing</th>
                    <?php foreach ($categories as $category_key => $category_name): ?>
                        <th class="manage-column"><?php echo esc_html($category_name); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listings)): ?>
                    <tr>
                        <td colspan="<?php echo count($categories) + 1; ?>">No listings found.</td>
                    </tr>
                <?php else: ?>
                   <?php foreach ($listings as $listing): ?>
					<?php 
						$hasValue = false;
						foreach ($categories as $category_key => $category_name): 
							// Retrieve the current vote count for each category
							$meta_key = 'endorsement_' . $category_key;
							$current_votes = get_post_meta($listing->ID, $meta_key, true);
							$current_votes = $current_votes ? intval($current_votes) : 0;

							if($current_votes > 0){
								$hasValue = true;
								break; // Exit the loop if a value greater than 0 is found
							}
						endforeach;
					?>
					<?php if ($hasValue): ?>
						<tr>
														<td>
						<a href="<?php echo get_permalink($listing->ID); ?>"><?php echo esc_html($listing->post_title); ?></a></td>
							<?php foreach ($categories as $category_key => $category_name): 
								// Retrieve the current vote count for each category
								$meta_key = 'endorsement_' . $category_key;
								$current_votes = get_post_meta($listing->ID, $meta_key, true);
								$current_votes = $current_votes ? intval($current_votes) : 0;
            ?>
                <td><?php echo intval($current_votes); ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <style>
        .wp-list-table th, .wp-list-table td {
            padding: 10px;
        }
        .wp-list-table th {
            background-color: #f5f5f5;
        }
        .wp-list-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .wp-list-table tbody tr:nth-child(even) {
            background-color: #fff;
        }
    </style>
    <?php
}


add_action('admin_menu', 'wpmax_endorsements_add_admin_menu');

add_filter('the_content', 'add_endorsement_categories_to_directory_listings');


?>
