<?php

/**
 * File Helper Controller
 *
 * @category  File Helper
 */

class FilehelperController extends BaseController
{
	/**
	 * Upload A file to the server
	 * @return null
	 */
	function uploadfile()
	{
		if (user_login_status() != true) { render_error("Login dulu", 401); }   // [GENC-25SEP26-AUDIT] dulu tanpa login
		if (!empty($this->post->fieldname)) { // Get Upload field name from post request
			$fieldname = $this->post->fieldname;
			if (!empty($this->file_upload_settings[$fieldname])) {
				$upload_settings = $this->file_upload_settings[$fieldname];
				$uploader = new Uploader;
				$upload_data = $uploader->upload($_FILES['file'], $upload_settings);
				if ($upload_data['hasErrors']) {
					$errors = $upload_data['errors'];
					render_error(json_encode($errors));
				}
				if ($upload_data['isComplete']) {
					$arr_files = $upload_data['data']['files'];
					if (!empty($upload_settings['returnfullpath'])) {
						$arr_files = array_map("set_url", $arr_files); // set files with complete url of the website
					}
					$uploaded_files = implode(",", $arr_files);
					echo $uploaded_files;
				}
			} else {
				render_error("No Upload settings found for the request", 404);
			}
		} else {
			render_error("Invalid Post Field Name", 400);
		}
	}

	function removefile()
	{
		// [GENC-25SEP26-AUDIT] dulu: siapa pun (tanpa login) bisa menghapus file APA SAJA di server lewat POST filepath.
		// Sekarang: wajib login & hanya file di dalam folder uploads/.
		if (user_login_status() != true) { render_error("Login dulu", 401); }
		if (!empty($this->post->filepath)) {
			try {
				$filepath = $this->post->filepath;
				$file_dir = str_ireplace(SITE_ADDR, "", $filepath);
				$real = realpath($file_dir);
				$up = realpath(ROOT . UPLOAD_DIR);
				if ($real === false || $up === false || strpos(str_replace('\\', '/', $real), str_replace('\\', '/', $up) . '/') !== 0 || !is_file($real)) { render_error("File tidak diizinkan", 403); }
				echo unlink($real);
			} catch (Exception $e) {
				echo 'Message: ' . $e->getMessage();
			}
		}
	}

	function resizeimg()
	{
		// [GENC-25SEP26-AUDIT] dulu membaca properti yang tidak ada ($this->src) -> Warning
		$img = isset($this->request->src) ? $this->request->src : null; //get image path from GET['src']
		$width = isset($this->request->w) ? $this->request->w : null; //get image width from GET['w']
		$height = isset($this->request->h) ? $this->request->h : null; //get image height from GET['h']
		if (empty($img)) { render_error("Parameter src kosong", 400); }
		set_img_src($img, $width, $height, 0, true);
	}

	function captcha()
	{
		//You can customize your captcha settings here

		$captcha_code = '';
		$captcha_image_height = 30;
		$captcha_image_width = 90;
		$total_characters_on_image = 6;

		//The characters that can be used in the CAPTCHA code.
		//avoid all confusing characters and numbers (For example: l, 1 and i)
		$possible_captcha_letters = "23456789ABCEFGHJKLMNPQRSTWXYZ";
		$captcha_font = ROOT . FONTS_DIR . "monofont.ttf";
		$random_captcha_dots = 50;
		$random_captcha_lines = 25;
		$captcha_text_color = "0x142864";
		
		//generate random chars from the possible letters
		$captcha_code = random_str(5, $possible_captcha_letters);

		$captcha_font_size = $captcha_image_height * 0.76;
		$captcha_image = @imagecreate(
			$captcha_image_width,
			$captcha_image_height
		);

		/* setting the background, text and noise colours here */
		$background_color = imagecolorallocate(
			$captcha_image,
			255,
			255,
			255
		);


		$captcha_text_color = imagecolorallocate(
			$captcha_image,
			90,50,90
		);


		$image_noise_color = imagecolorallocate(
			$captcha_image,
			250,120,200
		);

		/* Generate random dots in background of the captcha image */
		for ($count = 0; $count < $random_captcha_dots; $count++) {
			imagefilledellipse(
				$captcha_image,
				mt_rand(0, $captcha_image_width),
				mt_rand(0, $captcha_image_height),
				2,
				3,
				$image_noise_color
			);
		}

		/* Generate random lines in background of the captcha image */
		for ($count = 0; $count < $random_captcha_lines; $count++) {
			imageline(
				$captcha_image,
				mt_rand(0, $captcha_image_width),
				mt_rand(0, $captcha_image_height),
				mt_rand(0, $captcha_image_width),
				mt_rand(0, $captcha_image_height),
				$image_noise_color
			);
		}

		/* Create a text box and add 6 captcha letters code in it */
		$text_box = imagettfbbox(
			$captcha_font_size,
			10,
			$captcha_font,
			$captcha_code
		);
		$x = ($captcha_image_width - $text_box[4]) / 2;
		$y = ($captcha_image_height - $text_box[5]) / 2;
		imagettftext(
			$captcha_image,
			$captcha_font_size,
			10,
			$x,
			$y,
			$captcha_text_color,
			$captcha_font,
			$captcha_code
		);

		/* Show captcha image in the html page */
		// defining the image type to be shown in browser widow
		header('Content-Type: image/jpeg');
		imagejpeg($captcha_image); //showing the image
		imagedestroy($captcha_image); //destroying the image instance
		set_session("captcha", $captcha_code);
	}
}
