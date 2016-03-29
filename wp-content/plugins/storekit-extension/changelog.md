# StoreKit Changelog


=======
##1.8
### 25 February 2016

* **Enhancement** - Added FOUC fix to widgets. *MP*
* **Enhancement** - Added the category title and description to category listings. *MP*

=======
##1.7
### 03 February 2016

* **Tweak** - Removed the onboarding
* **Fix** - Added Mobile plugin and Social Commerce compatability. *MP*
* **Fix** - Unchecking Product Count may not have hidden the list product count in certain situations. *SOB*
* **Fix** - Fixed Category Header Image positioning in some child themes. *SOB*
* **Fix** - Fixed Product Image Flip when using .png's so the original is not visible through the other images. *SOB*

=======
##1.6
### 09 December 2015

* **Fix** - Unchecking Show Description was hiding buttons in the Product Category widget. *MP*
* **Fix** - Unchecking Show Title was hiding all content in the Product List widget. *MP*
* **Fix** - Fixed rounded image selector in the Product List Widget. *MP*
* **Fix** - Fixed rounded image selector in the Product Category widget. *MP*
* **Fix** - Fixed the Product Slider fade toggle. *MP*
* **Enhancement** - Added the ability to filter the product widget by `Items on Sale` and `Featured Items`. *MP*
* **Enhancement** - Added the mini cart to the header (including on/off toggle). *DP*
* **Tweak** - Renamed "Thumbnail" to "Display Product Images" in the customizer. *MP*
* **Tweak** - Renamed "Thumbnail Flip" to "Product Image Flip" in the customizer. *MP*

=======
##1.5
### 17 November 2015

* **Fix** - Pagination for the product widget is now fixed on all WP installs. *MP*

=======
##1.4
### 17 November 2015

* **Tweak** - Cart HTML is now 'after' the menu in order to align with new header flexbox css. *DP*
* **Fix** - Excerpt HTML now wrapped in a div to cater for p's in the RTE. *DP*

=======
##1.3
### 08 October 2015

* **Tweak** - Swapped slider divs around, placing copy-container before image-container, so that it works with new flexbox CSS. *DP*
* **Enhancement** - Added animation options to the slider, namely Fade, Slide or Parallax. *MP*
* **Enhancement** - Fixed the auto-height button in the slider. *MP*
* **Enhancement** - Updated to Swiper3 tech. *MP*

=======
##1.2
### 07 September 2015

* **Feature** - Thumbnail Flip on products in list flipping between feature image and first image in product gallery. *SOB*
* **Feature** - Product Category Images in the header of the category list pages. *SOB*
* **Changed** - Storekit CSS to load after Layers CSS. *SOB*
* **Fix** - Fixed 'sale' flash CSS ordering so it's always on top of product image. *SOB*
* **Fix** - Dequeue Select2 from fronted of site. *SOB*
* **Fix** - Erroneous numbers appearing between slides when adding multiple slides. *SOB*
* **Fix** - Duplicate slides being added by multiple clicks on create-slide button. *SOB*
* **Fix** - Extra slashes appearing in css or js enqueue's. *SOB*
* **Fix** - Transparent header background fix for the cart set to inline. *SOB*
* **Fix** - Fixed products in list archive breaking out of their row layout since Layers 1.2.4. *SOB*

=======
##1.1
### 17 August 2015

* **Fix** - 4.3 compatability fix. Widgets now use correct PHP constructors *MP*
* **Fix** - `true` showing on product single where 'stock count' should be showing. *SOB*
* **Fix** - Fixed the addition of Product Variations to the product slider. *MP*
* **Fix** - Importing pages with StoreKit widgets no longer throw any errors. *MP*
* **Enhancement** - Better header-cart CSS for different header options. *DP*