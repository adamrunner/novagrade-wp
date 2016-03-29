<section class="layers-area-wrapper">

    <div class="layers-onboard-wrapper">

        <div class="layers-onboard-controllers">
            <div class="onboard-nav-dots layers-pull-left" id="layers-onboard-anchors"></div>
            <a class="layers-button btn-link layers-pull-right" href="" id="layers-onboard-skip"><?php _e( 'Skip' , 'layers-storekit' ); ?></a>
        </div>

        <div class="layers-onboard-slider">

            <!-- STEP 1 -->
            <div class="layers-onboard-slide layers-animate layers-onboard-slide-current">
                <div class="layers-column layers-span-4 postbox">
                    <div class="layers-content-large">
                        <!-- Your content goes here -->
                        <div class="layers-section-title layers-small layers-no-push-bottom">
                            <div class="layers-push-bottom-small">
                                <small class="layers-label label-secondary">
                                    <?php _e( 'StoreKit' , 'layers-storekit' ); ?>
                                </small>
                            </div>
                            <h3 class="layers-heading">
                                <?php _e( 'Thanks for choosing StoreKit!' , 'layers-storekit' ); ?>
                            </h3>
                            <div class="layers-excerpt">
                                <p>
                                    <?php _e( 'Start by choosing options and settings for Menu Cart, Product List and the Product Page.' , 'layers-storekit' ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="layers-button-well">
                        <a class="layers-button btn-primary layers-pull-right onbard-next-step" href=""><?php _e( 'Got it, Next Step &rarr;' , 'layers-storekit' ); ?></a>
                    </div>
                </div>
                <div class="layers-column layers-span-8 no-gutter layers-demo-video">
                    <?php layers_show_html5_video( 'https://s3.amazonaws.com/cdn.oboxsites.com/layers/videos/storekit-toggles.mp4', 660 ); ?>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="layers-onboard-slide layers-animate layers-onboard-slide-inactive">
                <div class="layers-column layers-span-4 postbox">
                    <div class="layers-content-large">
                        <!-- Your content goes here -->
                        <div class="layers-section-title layers-small layers-no-push-bottom">
                            <div class="layers-push-bottom-small">
                                <small class="layers-label label-secondary">
                                    <?php _e( 'StoreKit' , 'layers-storekit' ); ?>
                                </small>
                            </div>
                            <h3 class="layers-heading">
                                <?php _e( 'Curate your products' , 'layers-storekit' ); ?>
                            </h3>
                            <div class="layers-excerpt">
                                <p>
                                    <?php _e( 'StoreKit is packaged with 3 widgets, Product List, Product Slider and Product Categories. Add them to the page just like you would in Layers, to create dynamic layouts with your WooCommerce content.' , 'layers-storekit' ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="layers-button-well">
                        <a class="layers-button btn-primary layers-pull-right" href="<?php echo admin_url( 'customize.php' ); ?>"><?php _e( 'Awesome, Let\'s Go &rarr;' , 'layerswp' ); ?></a>
                    </div>
                </div>
                <div class="layers-column layers-span-8 no-gutter layers-demo-video">
                    <?php layers_show_html5_video( 'https://s3.amazonaws.com/cdn.oboxsites.com/layers/videos/storekit-widgets.mp4', 660 ); ?>
                </div>
            </div>

        </div>

    </div>

</section>
