//<?php
/**
 * @brief		Hook on \IPS\core\modules\admin\applications\_plugins
 * @author		XENNTEC UG
 * @copyright	(c) 2026 XENNTEC UG
 * @package		X Bulk Dev Tools
 * @since		1.0.0
 */

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook475 extends _HOOK_CLASS_
{
	/**
	 * Override manage() to add Bulk Download Plugins sidebar button
	 *
	 * @return	void
	 */
	protected function manage()
	{
		try
		{
			parent::manage();

			if ( \IPS\IN_DEV )
			{
				\IPS\Output::i()->sidebar['actions']['xbdt_bulk_plugins'] = array(
					'icon'  => 'download',
					'link'  => \IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtBulkPlugins' ),
					'title' => 'xbdt_bulk_download_plugins',
					'data'  => array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_bulk_download_plugins' ) )
				);
			}
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Bulk Download Plugins — selection dialog
	 *
	 * @return	void
	 */
	protected function xbdtBulkPlugins()
	{
		try
		{
			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/P1', 403, '' );
			}

			$form = new \IPS\Helpers\Form;

			/* Download mode */
			$form->add( new \IPS\Helpers\Form\Radio( 'xbdt_plugin_download_mode', 'individual', TRUE, array(
				'options' => array(
					'individual' => 'xbdt_plugin_download_individual',
					'zip'        => 'xbdt_plugin_download_zip',
				),
			) ) );

			/* Plugin selection — all plugins listed, all pre-checked */
			$options = array();
			$defaultChecked = array();

			foreach ( \IPS\Plugin::plugins() as $plugin )
			{
				$options[ $plugin->id ] = $plugin->name;
				$defaultChecked[] = $plugin->id;
			}

			$form->add( new \IPS\Helpers\Form\CheckboxSet( 'xbdt_plugins', $defaultChecked, TRUE, array(
				'options' => $options,
			), function( $val ) {
				if ( empty( $val ) )
				{
					throw new \DomainException( 'xbdt_no_plugins_selected' );
				}
			} ) );

			/* Handle form submission */
			if ( $values = $form->values() )
			{
				$_SESSION['xbdt_plugin_ids']          = $values['xbdt_plugins'];
				$_SESSION['xbdt_plugin_download_mode'] = $values['xbdt_plugin_download_mode'];

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginProcess' )->csrf()
				);
			}

			\IPS\Output::i()->title  = \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_bulk_download_plugins' );
			\IPS\Output::i()->output = $form;
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Process plugin downloads via MultipleRedirect
	 *
	 * @return	void
	 */
	protected function xbdtPluginProcess()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/P2', 403, '' );
			}

			\IPS\Output::i()->output = new \IPS\Helpers\MultipleRedirect(
				\IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginProcess' )->csrf(),
				function( $data )
				{
					/* First call — initialize from session */
					if ( $data === NULL )
					{
						$pluginIds = isset( $_SESSION['xbdt_plugin_ids'] ) ? array_values( $_SESSION['xbdt_plugin_ids'] ) : array();
						if ( empty( $pluginIds ) )
						{
							return NULL;
						}
						$data = array(
							'index'     => 0,
							'plugins'   => $pluginIds,
							'total'     => \count( $pluginIds ),
							'errors'    => array(),
							'built'     => array(),
						);
					}

					/* All done? */
					if ( $data['index'] >= $data['total'] )
					{
						$_SESSION['xbdt_plugin_errors']   = $data['errors'];
						$_SESSION['xbdt_plugin_built']    = $data['built'];
						return NULL;
					}

					$pluginId = $data['plugins'][ $data['index'] ];
					$step     = ( $data['index'] + 1 ) . '/' . $data['total'];

					try
					{
						$plugin = \IPS\Plugin::load( $pluginId );

						/* Build the XML in a temp file so it's ready for download */
						$xml = $this->xbdtBuildPluginXml( $plugin );
						$tempPath = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/xbdt_plugin_' . $pluginId . '.xml';
						\file_put_contents( $tempPath, $xml->asXML() );

						$data['built'][] = array(
							'id'       => $pluginId,
							'name'     => $plugin->name,
							'version'  => $plugin->version_human,
							'tempFile' => $tempPath,
						);
					}
					catch ( \Exception $e )
					{
						$data['errors'][] = 'Plugin #' . $pluginId . ': ' . $e->getMessage();
					}

					$data['index']++;
					$pct = \intval( ( $data['index'] / $data['total'] ) * 100 );

					$pluginName = isset( $plugin ) ? $plugin->name : 'Plugin #' . $pluginId;
					$label = \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_exporting_plugin', FALSE, array( 'sprintf' => array( $pluginName, $data['index'], $data['total'] ) ) );

					return array( $data, $label, $pct );
				},
				function()
				{
					\IPS\Output::i()->redirect(
						\IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginDownloadResults' )
					);
				}
			);
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Plugin download results page
	 *
	 * @return	void
	 */
	protected function xbdtPluginDownloadResults()
	{
		try
		{
			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/P3', 403, '' );
			}

			$built  = isset( $_SESSION['xbdt_plugin_built'] ) ? $_SESSION['xbdt_plugin_built'] : array();
			$errors = isset( $_SESSION['xbdt_plugin_errors'] ) ? $_SESSION['xbdt_plugin_errors'] : array();
			$mode   = isset( $_SESSION['xbdt_plugin_download_mode'] ) ? $_SESSION['xbdt_plugin_download_mode'] : 'individual';

			if ( empty( $built ) )
			{
				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins' ),
					'xbdt_no_plugins_selected'
				);
			}

			$html = '';

			/* Show errors if any */
			if ( !empty( $errors ) )
			{
				$html .= '<div class="ipsMessage ipsMessage_warning">';
				$html .= '<strong>' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_errors_occurred' ) . '</strong><br>';
				foreach ( $errors as $error )
				{
					$html .= htmlspecialchars( $error ) . '<br>';
				}
				$html .= '</div><br>';
			}

			/* ZIP download button */
			$zipUrl = \IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginDownloadZip' )->csrf();
			$html .= '<p>' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_plugin_download_results_desc' ) . '</p>';
			$html .= '<div class="ipsButtonBar">';
			$html .= '<a href="' . $zipUrl . '" class="ipsButton ipsButton_primary ipsButton_medium"><i class="fa fa-file-archive-o"></i> &nbsp;' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_plugin_download_all_zip' ) . '</a>';
			$html .= '</div><br>';

			/* Individual download table */
			$html .= '<table class="ipsTable ipsTable_zebra">';
			$html .= '<thead><tr><th>Plugin</th><th>Version</th><th></th></tr></thead>';
			$html .= '<tbody>';

			foreach ( $built as $info )
			{
				$xmlUrl = \IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginDownloadXml&pluginId=' . $info['id'] )->csrf();

				$html .= '<tr>';
				$html .= '<td>' . htmlspecialchars( $info['name'] ) . '</td>';
				$html .= '<td>' . htmlspecialchars( $info['version'] ) . '</td>';
				$html .= '<td class="ipsTable_wrap"><a href="' . $xmlUrl . '" class="ipsButton ipsButton_light ipsButton_verySmall"><i class="fa fa-download"></i> &nbsp;' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_plugin_download_xml' ) . '</a></td>';
				$html .= '</tr>';
			}

			$html .= '</tbody></table>';

			/* If user chose individual mode, auto-start sequential downloads */
			if ( $mode === 'individual' )
			{
				$html .= '<script type="text/javascript">';
				$html .= '(function() {';
				$html .= '  var urls = [';
				$first = true;
				foreach ( $built as $info )
				{
					$xmlUrl = \IPS\Http\Url::internal( 'app=core&module=applications&controller=plugins&do=xbdtPluginDownloadXml&pluginId=' . $info['id'] )->csrf();
					if ( !$first ) { $html .= ','; }
					$html .= '"' . addslashes( $xmlUrl ) . '"';
					$first = false;
				}
				$html .= '  ];';
				$html .= '  var i = 0;';
				$html .= '  function downloadNext() {';
				$html .= '    if (i < urls.length) {';
				$html .= '      var iframe = document.createElement("iframe");';
				$html .= '      iframe.style.display = "none";';
				$html .= '      iframe.src = urls[i];';
				$html .= '      document.body.appendChild(iframe);';
				$html .= '      i++;';
				$html .= '      setTimeout(downloadNext, 1500);';
				$html .= '    }';
				$html .= '  }';
				$html .= '  setTimeout(downloadNext, 500);';
				$html .= '})();';
				$html .= '</script>';
			}

			\IPS\Output::i()->title  = \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_plugin_download_results' );
			\IPS\Output::i()->output = $html;
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Download a single plugin as XML
	 *
	 * @return	void
	 */
	protected function xbdtPluginDownloadXml()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/P4', 403, '' );
			}

			$pluginId = (int) \IPS\Request::i()->pluginId;

			/* Check if we have a pre-built temp file */
			$tempPath = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/xbdt_plugin_' . $pluginId . '.xml';
			if ( file_exists( $tempPath ) )
			{
				$output = \file_get_contents( $tempPath );
				@unlink( $tempPath );
			}
			else
			{
				/* Build on the fly */
				$plugin = \IPS\Plugin::load( $pluginId );
				$xml    = $this->xbdtBuildPluginXml( $plugin );
				$output = $xml->asXML();
			}

			$plugin  = \IPS\Plugin::load( $pluginId );
			$filename = $plugin->name . ' ' . $plugin->version_human . '.xml';

			\IPS\Output::i()->sendOutput( $output, 200, 'application/xml', array(
				'Content-Disposition' => \IPS\Output::getContentDisposition( 'attachment', $filename )
			), FALSE, FALSE, FALSE );
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Download all selected plugins as ZIP
	 *
	 * @return	void
	 */
	protected function xbdtPluginDownloadZip()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/P5', 403, '' );
			}

			$built = isset( $_SESSION['xbdt_plugin_built'] ) ? $_SESSION['xbdt_plugin_built'] : array();
			if ( empty( $built ) )
			{
				\IPS\Output::i()->error( 'xbdt_no_plugins_selected', '2XBDT/P6', 400, '' );
			}

			$zipPath  = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/xbdt_plugins_bulk_download.zip';
			$tempFiles = array();

			$zip = new \ZipArchive();
			if ( $zip->open( $zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) !== TRUE )
			{
				\IPS\Output::i()->error( 'Could not create ZIP archive', '2XBDT/P7', 500, '' );
			}

			foreach ( $built as $info )
			{
				$tempPath = $info['tempFile'];
				$xmlFilename = $info['name'] . ' ' . $info['version'] . '.xml';

				if ( file_exists( $tempPath ) )
				{
					$zip->addFile( $tempPath, $xmlFilename );
					$tempFiles[] = $tempPath;
				}
				else
				{
					/* Rebuild if temp file was already consumed */
					try
					{
						$plugin = \IPS\Plugin::load( $info['id'] );
						$xml    = $this->xbdtBuildPluginXml( $plugin );
						$newTempPath = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/xbdt_plugin_' . $info['id'] . '_zip.xml';
						\file_put_contents( $newTempPath, $xml->asXML() );
						$zip->addFile( $newTempPath, $xmlFilename );
						$tempFiles[] = $newTempPath;
					}
					catch ( \Exception $e )
					{
						\IPS\Log::log( $e, 'xbdt' );
						continue;
					}
				}
			}

			$zip->close();

			$output = \file_get_contents( $zipPath );

			/* Cleanup temp files */
			@unlink( $zipPath );
			foreach ( $tempFiles as $path )
			{
				@unlink( $path );
			}

			/* Clean up session */
			unset( $_SESSION['xbdt_plugin_ids'], $_SESSION['xbdt_plugin_built'], $_SESSION['xbdt_plugin_errors'], $_SESSION['xbdt_plugin_download_mode'] );

			$pluginNames = array_column( $built, 'id' );
			$zipName = 'X Bulk Dev Download Combined ' . mb_substr( md5( implode( ',', $pluginNames ) . time() ), 0, 8 ) . '.zip';
			\IPS\Output::i()->sendOutput( $output, 200, 'application/zip', array(
				'Content-Disposition' => \IPS\Output::getContentDisposition( 'attachment', $zipName )
			), FALSE, FALSE, FALSE );
		}
		catch ( \Error | \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Build plugin XML (mirrors core download logic without DB side-effects)
	 *
	 * @param	\IPS\Plugin	$plugin
	 * @return	\IPS\Xml\SimpleXML
	 */
	protected function xbdtBuildPluginXml( $plugin )
	{
		$xml = \IPS\Xml\SimpleXML::create('plugin');
		$xml->addAttribute( 'name', $plugin->name );
		$xml->addAttribute( 'version_long', $plugin->version_long );
		$xml->addAttribute( 'version_human', $plugin->version_human );
		$xml->addAttribute( 'author', $plugin->author );
		$xml->addAttribute( 'website', $plugin->website );
		$xml->addAttribute( 'update_check', $plugin->update_check );

		$pluginPath = \IPS\ROOT_PATH . '/plugins/' . $plugin->location;

		/* Hooks */
		$hooks = $xml->addChild( 'hooks' );
		foreach ( \IPS\Db::i()->select( '*', 'core_hooks', array( 'plugin=?', $plugin->id ) ) as $hook )
		{
			$hookFile = $pluginPath . '/hooks/' . $hook['filename'] . '.php';
			if ( file_exists( $hookFile ) )
			{
				$hookNode = $hooks->addChild( 'hook', \IPS\Plugin::addExceptionHandlingToHookFile( $hookFile ) );
				$hookNode->addAttribute( 'type', $hook['type'] );
				$hookNode->addAttribute( 'class', $hook['class'] );
				$hookNode->addAttribute( 'filename', $hook['filename'] );
			}
		}

		/* Settings */
		if ( file_exists( $pluginPath . '/dev/settings.json' ) )
		{
			$settings = json_decode( file_get_contents( $pluginPath . '/dev/settings.json' ), TRUE );
			if ( !empty( $settings ) )
			{
				$xml->addChild( 'settings', $settings );
			}
		}

		/* Uninstall code */
		if ( file_exists( $pluginPath . '/uninstall.php' ) )
		{
			$xml->addChild( 'uninstall', file_get_contents( $pluginPath . '/uninstall.php' ) );
		}

		/* Settings code */
		if ( file_exists( $pluginPath . '/settings.php' ) )
		{
			$xml->addChild( 'settingsCode', file_get_contents( $pluginPath . '/settings.php' ) );
		}

		/* Tasks */
		if ( file_exists( $pluginPath . '/dev/tasks.json' ) )
		{
			$tasksNode = $xml->addChild( 'tasks' );
			foreach ( json_decode( file_get_contents( $pluginPath . '/dev/tasks.json' ), TRUE ) as $key => $frequency )
			{
				$taskFile = $pluginPath . '/tasks/' . $key . '.php';
				if ( file_exists( $taskFile ) )
				{
					$taskNode = $tasksNode->addChild( 'task', file_get_contents( $taskFile ) );
					$taskNode->addAttribute( 'key', $key );
					$taskNode->addAttribute( 'frequency', $frequency );
				}
			}
		}

		/* Widgets */
		if ( file_exists( $pluginPath . '/dev/widgets.json' ) )
		{
			$widgetsNode = $xml->addChild( 'widgets' );
			foreach ( json_decode( file_get_contents( $pluginPath . '/dev/widgets.json' ), TRUE ) as $key => $json )
			{
				$widgetFile = $pluginPath . '/widgets/' . $key . '.php';
				if ( file_exists( $widgetFile ) )
				{
					$content = file_get_contents( $widgetFile );
					$content = str_replace( "namespace IPS\\plugins\\{$plugin->location}\\widgets", "namespace IPS\\plugins\\<{LOCATION}>\\widgets", $content );
					$content = str_replace( "public \$plugin = '{$plugin->id}';", "public \$plugin = '<{ID}>';", $content );
					$content = str_replace( "public \$app = '';", "", $content );
					$widgetNode = $widgetsNode->addChild( 'widget', $content );
					$widgetNode->addAttribute( 'key', $key );
					foreach ( $json as $dataKey => $value )
					{
						if ( \is_array( $value ) )
						{
							$value = implode( ",", $value );
						}
						$widgetNode->addAttribute( $dataKey, $value );
					}
				}
			}
		}

		/* HTML, CSS, JS, Resources */
		foreach ( array( 'html' => 'phtml', 'css' => 'css', 'js' => 'js', 'resources' => '*' ) as $k => $ext )
		{
			$dirPath = $pluginPath . '/dev/' . $k;
			if ( is_dir( $dirPath ) )
			{
				$resourcesNode = $xml->addChild( "{$k}Files" );
				foreach ( new \DirectoryIterator( $dirPath ) as $file )
				{
					if ( !$file->isDot() and mb_substr( $file, 0, 1 ) != '.' and ( $ext === '*' or mb_substr( $file, -( mb_strlen( $ext ) + 1 ) ) === ".{$ext}" ) AND $file != 'index.html' )
					{
						$content = file_get_contents( $file->getPathname() );
						$resourcesNode->addChild( $k, base64_encode( $content ) )->addAttribute( 'filename', $file );
					}
				}
			}
		}

		/* Language strings */
		if ( file_exists( $pluginPath . '/dev/lang.php' ) or file_exists( $pluginPath . '/dev/jslang.php' ) )
		{
			$langNode = $xml->addChild( 'lang' );
			foreach ( array( 'lang' => 0, 'jslang' => 1 ) as $file => $js )
			{
				if ( file_exists( $pluginPath . "/dev/{$file}.php" ) )
				{
					$lang = array();
					require $pluginPath . "/dev/{$file}.php";
					foreach ( $lang as $lk => $lv )
					{
						$word = $langNode->addChild( 'word', $lv );
						$word->addAttribute( 'key', $lk );
						$word->addAttribute( 'js', $js );
					}
				}
			}
		}

		/* Versions */
		if ( file_exists( $pluginPath . '/dev/versions.json' ) )
		{
			$versionsNode = $xml->addChild( 'versions' );
			$versions = json_decode( file_get_contents( $pluginPath . '/dev/versions.json' ), TRUE );
			ksort( $versions );
			foreach ( $versions as $vk => $vv )
			{
				$setupFile = ( $vk == 10000 ) ? 'install.php' : $vk . '.php';
				$setupPath = $pluginPath . '/dev/setup/' . $setupFile;
				$node = $versionsNode->addChild( 'version', file_exists( $setupPath ) ? file_get_contents( $setupPath ) : '' );
				$node->addAttribute( 'long', $vk );
				$node->addAttribute( 'human', $vv );
			}
		}

		return $xml;
	}
}
