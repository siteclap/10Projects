<?php
/**
 * Site Footer
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;
?>

<footer class="tp-footer">
	<div class="tp-footer-grid">
		<div>
			<h4>About</h4>
			<p style="font-size:14px;line-height:1.7;">
				10Projects uses AI to find your 10 best-fit properties from hundreds of options. Unbiased analysis, transparent scoring.
			</p>
		</div>
		<div>
			<h4>Quick Links</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">All Projects</a></li>
				<li><a href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>">Our Methodology</a></li>
				<li><a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Buyer Guides</a></li>
				<li><a href="<?php echo esc_url( home_url( '/emi-calculator/' ) ); ?>">EMI Calculator</a></li>
			</ul>
		</div>
		<div>
			<h4>Locations</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/kharghar/' ) ); ?>">Kharghar</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/panvel/' ) ); ?>">Panvel</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/ulwe/' ) ); ?>">Ulwe</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/vashi/' ) ); ?>">Vashi</a></li>
			</ul>
		</div>
		<div>
			<h4>Contact</h4>
			<ul>
				<li><a href="mailto:hello@10projects.com">hello@10projects.com</a></li>
				<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Service</a></li>
				<li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>">Disclaimer</a></li>
			</ul>
		</div>
	</div>
	<div class="tp-footer-bottom">
		&copy; <?php echo date( 'Y' ); ?> 10Projects. All rights reserved.
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
