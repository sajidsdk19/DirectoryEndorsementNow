WPMax Directory Listings Endorsements Plugin


Overview
============
The WPMax Directory Listings Endorsements Plugin enhances your WPMax directory listings by adding endorsement categories. Users can endorse listings based on various criteria, providing a community-driven way to highlight exceptional services or features.

Features
============
Endorsement Categories: Includes predefined categories such as Coaching, Facilities, Communication, and Value for Money.
User Voting: Allows users to vote for listings in specific categories.
AJAX Handling: Utilizes AJAX for smooth and interactive voting experiences.
Vote Tracking: Tracks votes to prevent multiple votes from the same user for the same category.
See How many votes have been voted by users on dasboard only by ADMIN . 


Installation
============
Download the plugin ZIP file from the repository.
Go to your WordPress admin dashboard.
Navigate to Plugins > Add New > Upload Plugin.
Upload the ZIP file and click "Install Now."
Activate the plugin after installation.


- Goto Plugin File Editor 
-Edit 
directorist/templates/single-reviews.php (active)

add this line on line number  11
if (function_exists('display_endorsement_categories')) {
                display_endorsement_categories(get_the_ID());
            }


- Goto Theme File editor 

- Make sure to add this line at the bottom /must be line 74 :)  

// Function to output endorsement categories
add_filter('the_content', 'add_endorsement_categories_to_directory_listings');

Configuration
No additional configuration required: The plugin adds endorsement categories to the directory listings automatically upon activation.
Usage
Viewing Endorsements: Navigate to a directory listing on the front end of your website.
Voting: Users can click on categories to vote. The vote count updates in real time, and categories change style based on vote status.



Requirements
============
WordPress
WPMax Directory Plugin
Support
For issues or feature requests, please open an issue in the GitHub repository.

License
This plugin is free for use with Wp Directory Listing plugin developed by Sajid Khan  
