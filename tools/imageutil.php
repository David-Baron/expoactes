<?PHP

function linkjpg($texte)   // transforme en lien actif les nom de fichier JPG rencontrés (séparés par des , ou des blancs)
	{
	// adapté pour http://marmottesdesavoie.org/

	$result = '';
	$cpt = 0;

	if ($texte !="");
		{
		$longref = strlen($texte);
		$suffixe = mb_substr(strrchr($texte,"_"),1);
		$longsuffixe = strlen($suffixe);
		$longprefixe = $longref-$longsuffixe;
		$prefixe = mb_substr($texte,0,$longprefixe);


		//CAS 00A
		If ($longsuffixe==3)
			{
			$image=URK_JPG.$texte.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}


		//CAS 00A-00B
		If ($longsuffixe ==7)
			{
			$prem=mb_substr($suffixe,0,3);
			$dern=mb_substr($suffixe,4,3);
			$nb=$dern-$prem;
			echo '  <u>Photos </u> : ';

			for ($k = 0; $k <= $nb; $k++)
				{ 
				$index2=$prem+$k;
				$index1 = "000".$index2;
				$index = mb_substr($index1,-3);
				$image=URK_JPG.$prefixe.$index.".jpg";	
				$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
				}
			}


		//CAS 00Aet00B
		If ($longsuffixe ==8)
			{
			$prem=mb_substr($suffixe,0,3);
			$dern=mb_substr($suffixe,5,3);
			$image=URK_JPG.$prefixe.$prem.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			$image=URK_JPG.$prefixe.$dern.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}

		//CAS  00Aet00B-00C et 00A-00Bet00C
		IF ($longsuffixe >8)
			{
			$test=mb_substr($suffixe,3,1);
			if ($test =="e")

			// Cas 00Aet00B-00C
				{
				$prem=mb_substr($suffixe,5,3);
				$dern=mb_substr($suffixe,-3);
				$extra=mb_substr($suffixe,0,3);
				$nb=$dern-$prem;
				$image=URK_JPG.$prefixe.$extra.".jpg";	
				$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
				for ($k = 0; $k <= $nb; $k++)
					{ 
					$index2=$prem+$k;
					$index1 = "000".$index2;
					$index = mb_substr($index1,-3);
					$image=URK_JPG.$prefixe.$index.".jpg";	
					$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
					}

				}
			 else

			// Cas 00A-00Bet00C
				{
				$prem=mb_substr($suffixe,0,3);
				$dern=mb_substr($suffixe,4,3);
				$extra=mb_substr($suffixe,-3);
				$nb=$dern-$prem;

				for ($k = 0; $k <= $nb; $k++)
					{ 
					$index2=$prem+$i;
					$index1 = "000".$index2;
					$index = mb_substr($index1,-3);
					$image=URK_JPG.$prefixe.$index.".jpg";	
					$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
					}
				$image=URK_JPG.$prefixe.$extra.".jpg";	
				$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
				}
			}
		}
	return $result;  	
	}


?>