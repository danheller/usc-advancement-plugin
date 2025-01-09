<?php
/**
 * Template Name: Signed image release (PDF version)
 *
 * @package uscadvance
 */
 
//echo  $_GET['r'];
if( isset( $_GET['r'] ) && $_GET['r'] && 'release' == get_post_type( intval( $_GET['r'] ) ) ) {
	$release_id = intval( $_GET['r'] );

	require( ADVANCE_PATH . 'functions/fpdf/fpdf.php' );
	
	class PDF extends FPDF
	{
	// Page header
	function Header()
	{
		// Logo
		$this->Image( ADVANCE_URL . 'images/logo-usc-gateway.png',84,10,45);

		$this->SetDrawColor(0,0,0);
		$this->Line(20, 30, 210-20, 30);

		$this->Ln(22);

		// Arial bold 15
		$this->SetFont('Arial','B',20);
		// Move to the right
		// Title
		$this->Cell(0,15,'Image Release Form',0,2,'C');
		// Line break
		$this->Ln(2);

	}
	
	// Page footer
	function Footer()
	{
//		// Position at 1.5 cm from bottom
//		$this->SetY(-15);
//		// Arial italic 8
//		$this->SetFont('Arial','I',8);
//		// Page number
//		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
	}
	
	// Instanciation of inherited class
	$pdf = new PDF('P', 'mm', 'A4');
//	$pdf->SetCellMargin(4);
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','',9);

	$pdf->Cell(10);

	$pdf->MultiCell(170,5,'I hereby irrevocably consent to and authorize the use by the University of Southern California, a California non-profit corporation ("USC"), of any and all photographs, video, voice recordings or other media taken of me, including derivative works thereof (collectively, the "Images"), and any reproduction of them in any form in any media whatsoever, whether now known or hereafter created, throughout the world in perpetuity.',0,'L');
	$pdf->Cell(0,3,'',0,2);$pdf->Cell(10);
	$pdf->MultiCell(170,5,'I also consent to the use of my name or likeness, or an assigned fictitious name, in connection with the exhibition, distribution, merchandising, advertising, exploiting and/or publicizing of Images for USC.',0,'L');
	$pdf->Cell(0,3,'',0,2);$pdf->Cell(10);
	$pdf->MultiCell(170,5,'I hereby release and discharge USC, its trustees, officers, employees, licensees and affiliates from any and all claims, actions, suits or demands of any kind or nature whatsoever, in connection with the use of Images and the reproduction thereof as aforesaid. I understand and agree that USC will be the exclusive owner of all rights, including, but not limited to, all copyrights, in and to the Images in whole or part, throughout the universe, in perpetuity, in any medium now known or hereafter developed, and to license others to so use them in any manner USC may determine at its sole discretion, without any obligation to me.',0,'L');
	$pdf->Cell(0,3,'',0,2);$pdf->Cell(10);
	$pdf->MultiCell(170,5,'I hereby waive any right that I may have to inspect and/or approve the use of the Images or any reproductions thereof by USC.',0,'L');

	$pdf->Cell(0,5,'',0,2);$pdf->Cell(10);

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Date: ',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(142,8,' '.date('F j, Y', strtotime(get_post_meta($release_id,'date',true) ) ),1,1);

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Name: ',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(142,8,' '.get_post_meta($release_id,'signedby',true),1,1);

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Address: ',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(142,8,' '.get_post_meta($release_id,'address',true),1,'L');

	$pdf->Cell(0,2,'',0,2);$pdf->Cell(38);

	$pdf->Cell(71,8,' '.get_post_meta($release_id,'city',true),1,0);
	$pdf->Cell(4.5);
	$pdf->Cell(31,8,' '.get_post_meta($release_id,'state',true),1,0);
	$pdf->Cell(4.5);
	$pdf->Cell(31,8,' '.get_post_meta($release_id,'zip',true),1,0);
	$pdf->Cell(0,8,'',0,1);$pdf->Cell(37);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(71,8,'City',0,0);
	$pdf->Cell(4.5);
	$pdf->Cell(31,8,'State',0,0);
	$pdf->Cell(4.5);
	$pdf->Cell(31,8,'Zip Code',0,2);

	$pdf->Cell(0,4,'',0,1);$pdf->Cell(10);

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Phone:',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(142,8,' '.get_post_meta($release_id,'phone',true),1,1);

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);	

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Email: ',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(142,8,' '.get_post_meta($release_id,'email',true),1,1);

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);	

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(28,8,'Signature: ',0,2);

	$wp_upload_dir = wp_get_upload_dir();
	$wp_upload_dir = $wp_upload_dir['path'];
	$temploc = $wp_upload_dir . '/signature.png';
	$dataURI    = get_post_meta($release_id,'signature',true);
	$dataPieces = explode(',',$dataURI);
	$encodedImg = $dataPieces[1];
	$decodedImg = base64_decode($encodedImg);

	//  Check if image was properly decoded
	if( $decodedImg!==false ) {
		//  Save image to a temporary location
		if( file_put_contents($temploc,$decodedImg)!==false )
		{
			//  Open new PDF document and print image
			$pdf->Image($temploc,46,208,60);
			
			//  Delete image from server
			unlink($temploc);
		}
	}

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);	
	$pdf->Line(20, 236, 210-20, 236);
	$pdf->Ln(18);
	$pdf->Cell(9);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(170,5,'If above named is a minor child, a parent/guardian must sign.',0,'L');
	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);	

	
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(50,8,'Parent/Guardian Name: ',0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(120,8,' '.get_post_meta($release_id,'parent',true),1,1);

	$pdf->Cell(0,4,'',0,2);$pdf->Cell(10);	


	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(40,8,'Parent/Guardian Signature: ',0,2);

	$temploc = $wp_upload_dir . '/parentsignature.png';
	$dataURI    = get_post_meta($release_id,'parentsignature',true);
	$dataPieces = explode(',',$dataURI);
	$encodedImg = $dataPieces[1];
	$decodedImg = base64_decode($encodedImg);

	//  Check if image was properly decoded
	if( $decodedImg!==false ) {
		//  Save image to a temporary location
		if( file_put_contents($temploc,$decodedImg)!==false )
		{
			//  Open new PDF document and print image
			$pdf->Image($temploc,70,260,60);
			
			//  Delete image from server
			unlink($temploc);
		}
	}

	$pdf->Output();

} else {
	die();
}