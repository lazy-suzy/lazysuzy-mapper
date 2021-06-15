<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cb extends CI_Controller
{

	public function index()
	{

		//Store the get request
		$status = $this->input->get();

		//Initialize CNB Module
		$this->load->library('CNB', array(
			'proxy' => '5.79.66.2:13010',
			'debug' => false,
		));

		//Take relevent action
		if (isset($status['category'])) {
			header('Content-Type: application/json');
			echo json_encode($this->cnb->get_category($status['category']));
		} else if (isset($status['product'])) {
			header('Content-Type: application/json');
			echo json_encode($this->cnb->get_product($status['product']));
		} else if (isset($status['variations'])) {
			header('Content-Type: application/json');
			echo json_encode($this->cnb->get_variations($status['variations']));
		} else if (isset($status['category_id'])) {
			header('Content-Type: application/json');
			echo json_encode($this->cnb->get_category_by_id($status['category_id']));
		} else if (isset($status['category_url'])) {
			header('Content-Type: application/json');
			echo json_encode($this->cnb->get_category_id($status['category_url']));
		} else {
			$urls = [
				'/kids/tables-chairs'
			];

			$RETRY_COUNT = 5;
			foreach ($urls as $url) {

				$id = $this->cnb->get_category_id($url);
				$id = json_decode(json_encode($id));
				while (!isset($id->CategoryID) && $RETRY_COUNT >= 0) {
					echo "retry...\n";
					$RETRY_COUNT--;
					$id = $this->cnb->get_category_id($url);
				}
				$id = json_decode(json_encode($id));

				if (isset($id->CategoryID)) {
					$insert = [
						'url' => $url,
						'cat_id' => $id->CategoryID,
						'is_active' => 1
					];

					$this->db->insert('cab_category_urls', $insert);
					echo $url, " ", $id->CategoryID . "\n\n";
				}
			}

			/*echo '
				<h3>Example Categories</h3>
					<ul>
					    <li><a href="?category=living-room-furniture/furniture">living room furniture</a></li>
					    <li><a href="?category=sofas/furniture">sofas</a></li>
					    <li><a href="?category=sectionals/furniture">sectionals</a></li>
					    <li><a href="?category=chairs/furniture">accent chairs</a></li>
					    <li><a href="?category=ottomans-benches/furniture">ottomans, poufs, stools</a></li>
					    <li><a href="?category=benches/furniture">benches</a></li>
					    <li><a href="?category=coffee-tables/furniture">coffee tables</a></li>
					    <li><a href="?category=accent-tables/furniture">side tables</a></li>
					    <li><a href="?category=console-tables/furniture">console tables</a></li>
					    <li><a href="?category=storage-cabinets/furniture">storage cabinets</a></li>
					    <li><a href="?category=storage/furniture">media storage</a></li>
					    <li><a href="?category=bookcases/furniture">bookcases</a></li>
					    <li><a href="?category=bar-carts/furniture">bar carts, dining storage</a></li>
					    <li><a href="?category=dining-room-furniture/furniture">dining room furniture</a></li>
					    <li><a href="?category=dining-tables/furniture">dining tables</a></li>
					    <li><a href="?category=dining-chairs/furniture">dining chairs</a></li>
					    <li><a href="?category=bar-stools/furniture">bar stools, counter stools</a></li>
					    <li><a href="?category=office-furniture/furniture">office furniture</a></li>
					    <li><a href="?category=bedroom-furniture/furniture">bedroom furniture</a></li>
					    <li><a href="?category=beds/furniture">beds</a></li>
					    <li><a href="?category=dressers-chests/furniture">dressers, chests</a></li>
					    <li><a href="?category=nightstands/furniture">nightstands</a></li>
					    <li><a href="?category=outdoor-furniture/furniture">outdoor furniture</a></li>
					</ul>
				<h3>Example Products</h3>
					<ul>
					    <li><a href="?product=remy-charcoal-grey-wood-base-sofa/s208894">remy-charcoal-grey-wood-base-sofa</a></li>
						<li><a href="?product=remy-white-wood-base-sofa/s208873">remy-white-wood-base-sofa</a></li>
						<li><a href="?product=vicente-faux-shearling-sofa/s215668">vicente-faux-shearling-sofa</a></li>
						<li><a href="?product=vicente-teal-velvet-sofa/s215513">vicente-teal-velvet-sofa</a></li>
						<li><a href="?product=forte-channeled-saddle-leather-sofa/s193378">forte-channeled-saddle-leather-sofa</a></li>
						<li><a href="?product=forte-channeled-charcoal-velvet-sofa/s193220">forte-channeled-charcoal-velvet-sofa</a></li>
						<li><a href="?product=lenyx-stone-sofa/s203425">lenyx-stone-sofa</a></li>
						<li><a href="?product=cadet-black-leather-sofa/s215677">cadet-black-leather-sofa</a></li>
						<li><a href="?product=hoxton-grey-sofa/s173677">hoxton-grey-sofa</a></li>
						<li><a href="?product=hoxton-olive-green-leather-sofa/s129632">hoxton-olive-green-leather-sofa</a></li>
						<li><a href="?product=hoxton-black-leather-sofa/s676254">hoxton-black-leather-sofa</a></li>
						<li><a href="?product=avec-leather-sofa-with-brass-legs/s289970">avec-leather-sofa-with-brass-legs</a></li>
						<li><a href="?product=avec-leather-sofa-with-brushed-stainless-steel-legs/s289995">avec-leather-sofa-with-brushed-stainless-steel-legs</a></li>
						<li><a href="?product=avec-leather-apartment-sofa-with-brass-legs/s289888">avec-leather-apartment-sofa-with-brass-legs</a></li>
						<li><a href="?product=avec-leather-apartment-sofa-with-brushed-stainless-steel-legs/s289897">avec-leather-apartment-sofa-with-brushed-stainless-steel-legs</a></li>
						<li><a href="?product=piazza-leather-sofa/s290073">piazza-leather-sofa</a></li>
						<li><a href="?product=piazza-leather-apartment-sofa/s290069">piazza-leather-apartment-sofa</a></li>
						<li><a href="?product=curvo-pink-velvet-sofa/s666732">curvo-pink-velvet-sofa</a></li>
						<li><a href="?product=curvo-light-grey-velvet-sofa/s684448">curvo-light-grey-velvet-sofa</a></li>
						<li><a href="?product=logan-grey-boucle-sofa/s670895">logan-grey-boucle-sofa</a></li>
						<li><a href="?product=logan-brown-leather-sofa/s652506">logan-brown-leather-sofa</a></li>
						<li><a href="?product=drops-leather-sofa/s652581">drops-leather-sofa</a></li>
					</ul>
				<h3>Example Variations</h3>
					<ul>
					    <li><a href="?variations=371655">remy-charcoal-grey-wood-base-sofa</a></li>
					</ul>';*/
		}
	}
}
