<?php

	
	$app->group('/torrents', $authenticate($app), function() use($app) {
		
		$app->get('/', function() use ($app) {
			$torrentCloud = new TorrentCloud();
			$user = new User($_SESSION['username']);
			$user->torrents = $torrentCloud->pollTorrents($user->torrents);
			$user->update();

			$viewData['torrents'] = $user->torrents;
			$viewData['timeout'] = $torrentCloud->calculatePollTimeout($user->torrents);
			$app->view()->setData($viewData);
			$app->render('torrents.html');
		});

		$app->get('/poll', function() use($app) {
			$user = new User($_SESSION['username']);
			$torrentCloud = new TorrentCloud();
			$user->torrents = $torrentCloud->pollTorrents($user->torrents);
			$timeout = $torrentCloud->calculatePollTimeout($user->torrents);
			$user->update();
			
			$app->response->headers->set('Content-Type', 'application/json');
			echo json_encode(array("timeout"=>$timeout, "torrents"=>$user->torrents));
			
		});

		$app->post('/add', function() use ($app) { 
			$user = new User($_SESSION['username']);
			$torrentCloud = new TorrentCloud();
			$torrent = $torrentCloud->fetchTorrent($_POST['url']);
			$user->addTorrent($torrent);

			$view = $app->view();
			$view->setData(array("torrent"=>$torrent));
			$torrentHtml = $view->render('torrents/torrent.html');

			$app->response->headers->set('Content-Type', 'application/json');
			echo json_encode(array("timeout"=>DOWNLOADING_POLL_TIME, "torrent"=>$torrentHtml));
		});

		$app->post('/remove', function() use($app) {
			$user = new User($_SESSION['username']);
			$torrentCloud = new TorrentCloud();
			$torrent = $user->torrents[$_POST['torrentHash']];
			$torrentCloud->removeTorrent($torrent);
			$hashRemoved = $user->removeTorrent($torrent);

			echo $hashRemoved;

		});

	});

	

?>