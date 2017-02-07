<?php
header("Access-Control-Allow-Origin: *");

$arquivo = 'Main.miniwiki.txt';
$senha = 'admin';
$pasta = 'miniwiki/';

if (isset($_GET['fetch'])) {
	$paginas = array();
	foreach (scandir('./'.$pasta) as $pag) {
		if (substr($pag,-13) == '.miniwiki.txt') {
			$pag = substr($pag,0,-13);
			$paginas[] = Array('arquivo' => $pag);
		}
	}
	echo json_encode($paginas);
	exit();
}

if (isset($_GET['arquivo'])) {
	$arquivo = $_GET['arquivo'] . '.miniwiki.txt';
}

if ($_POST) {
	if (($_POST['texto']) && ($_POST['senha'] == $senha)) {
		file_put_contents($pasta.$arquivo,$_POST['texto']);
	}
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<link rel="stylesheet" type="text/css" href="miniwiki.css"/>
<title>MiniWiki - <?php echo substr($arquivo,0,-13); ?></title>
</head>
<body>
<div id="sidebar">
<b>MiniWiki</b>
<ul>
<?php
	foreach (scandir('./'.$pasta) as $pag) {
		if (substr($pag,-13) == '.miniwiki.txt') {
			$pag = substr($pag,0,-13);
			echo '<li><a href="?arquivo=' . $pag . '">' . $pag . '</a></li>';
		}
	}
?>
</ul>
<form method="get"><input type="text" name="arquivo"/><input type="submit" value=">"/></form>
</div>
<div id="conteudo">
<?php
foreach (file($pasta.$arquivo,FILE_IGNORE_NEW_LINES) as $linha) {
	if (substr($linha,0,4) == "####") echo '<h4>' . substr($linha,4) . '</h4>';
	elseif (substr($linha,0,3) == "###") echo '<h3>' . substr($linha,3) . '</h3>';
	elseif (substr($linha,0,2) == "##") echo '<h2>' . substr($linha,2) . '</h2>';
	elseif (substr($linha,0,1) == "#") echo '<h1>' . substr($linha,1) . '</h1>';
	elseif ($linha == "***") echo '<hr/>';


	elseif (substr($linha,0,2) == "**") echo $linha . '<br/>';

	elseif ((substr($linha,0,2) == "* ") || (substr($linha,0,2) == "+ ") || (substr($linha,0,2) == "- ")) {
		if (!$flag['ul']) {
			$flag['ul'] = true;
			echo '<ul>';
		}
		echo '<li>' . substr($linha,1) . '</li>';
	}
	elseif (substr($linha,0,4) == "    ") {
		if (!$flag['pre']) {
			$flag['pre'] = true;
			echo '<pre>' . substr($linha,4);
		}
		else echo "\n" . substr($linha,4);
	}
	elseif ($linha == "[[@ListPages]]") {
		echo '<ul>';
		foreach (scandir('./'.$pasta) as $pag) {
			if (substr($pag,-13) == '.miniwiki.txt') {
				$pag = substr($pag,0,-13);
				echo '<li><a href="?arquivo=' . $pag . '">' . $pag . '</a></li>';
			}
		}
		echo '</ul>';
	}
	elseif ((substr($linha,0,11) == "[[@IndexOf:") && (substr($linha,-2) == "]]")) {
		$indice = substr($linha,11,(strlen($linha) -13));
		echo '<div class="indexof"><ul>';
		foreach (scandir('./'.$pasta) as $pag) {
			if ((substr($pag,0,strlen($indice)) == $indice) && (substr($pag,-13) == '.miniwiki.txt')) {
				$pag = substr($pag,0,-13);
				echo '<li><a href="?arquivo=' . $pag . '">' . $pag . '</a></li>';
			}
		}
		echo '</ul></div>';
	}
	elseif ($linha == "[[@Help]]") {
		?>
		<h2>Elementos HTML</h2>
		<ul>
			<li><b>h1, h2, h3, h4:</b> #Texto, ##Texto, ###Texto, ####Texto</li>
			<li><b>hr:</b> ***</li>
			<li><b>pre:</b> 4 espaços seguidos</li>
			<li><b>ul:</b> * Item, - Item, + Item</li>
		</ul>
		<h2>MiniWiki</h2>
		<ul>
			<li><b>Listar todas as páginas:</b> [[@ListPages]]</li>
			<li><b>Listar páginas começando por "Texto":</b> [[@IndexOf:Texto]]</li>
			<li><b>Mostrar esta ajuda:</b> [[@Help]]</li>
		</ul>
		<?php
	}

	else {
		limpatags($flag);
		echo $linha . '<br/>';
	}
}

limpatags($flag,true);


function limpatags(&$flag, $force = false) {
	if ($flag['pre']) { $flag['pre'] = false; echo '</pre>'; }
	if ($flag['ul']) { $flag['ul'] = false; echo '</ul>'; }

	if ($force) {
		if ($flag['b']) { $flag['b'] = false; echo '</b>'; }
	}
}

?>
</div>
<hr/>
<form method="post" style="text-align: center">
	<textarea name="texto" style="width: 80%; height: 20em; resize: vertical;"/><?php echo file_get_contents($pasta.$arquivo); ?></textarea><br/>
	<input type="password" name="senha" placeholder="Senha"/><input type="submit" value="Salvar"/>
</form>
<small style="text-align: right; display: block; margin-top: 50px">MiniWiki by thoso</small>
</body>
</html>
