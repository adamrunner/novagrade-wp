        <footer id="footer" class="">
            <div id="bottom">
                <h6>Have a question? Please give us a call at <strong>+1 (503) 510 7560</strong></h6>
                <ul>
                    <li><a href="https://www.facebook.com/novagrade" target="_blank" class="facebook" title="Facebook profile">Facebook</a></li>
                    <li><a href="https://instagram.com/novagrade/" target="_blank" class="instagram" title="Instrgram profile">Instrgram</a></li>
                </ul>
            </div>
            <p>Copyright <?php echo date("Y"); ?> Novagrade Digiscoping Adapters</p>
            <div class="small-menu hide-for-small">
                <?php dynamic_sidebar( 'footer-widgets' ); ?>
            </div>
        </footer>

    </section>
</div>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48214827-1', 'auto');
  ga('require', 'linkid', 'linkid.js');
  ga('send', 'pageview');


//    $(window).scroll(function () { 
//       if ($(window).scrollTop() >= $(document).height() - $(window).height() - 10) {
//          if ( $( "#footer" ).is( ":hidden" ) ) {
//            $( "#footer" ).show( "slow" );
//          } else {
//            
//          }
//       }
//        var lastScrollTop = 0;
//        $(window).scroll(function(event){
//           var st = $(this).scrollTop();
//           if (st > lastScrollTop){
               
//           } else {
//              $( "#footer" ).hide( "slow" );
//           }
//           lastScrollTop = st;
//        });
//    });

</script>
<?php wp_footer(); ?>
</body>
</html>