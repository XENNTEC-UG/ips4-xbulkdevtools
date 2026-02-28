//<?php
/**
 * @brief		Hook on \IPS\core\modules\admin\applications\_applications
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

class hook474 extends _HOOK_CLASS_
{
	/**
	 * Override manage() to replace Build All sidebar button
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
				\IPS\Output::i()->sidebar['actions']['build_all'] = array(
					'icon'  => 'cogs',
					'link'  => \IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtBulkTools' ),
					'title' => 'xbdt_bulk_dev_tools',
					'data'  => array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_bulk_dev_tools' ) )
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
	 * Bulk Dev Tools — selection dialog
	 *
	 * @return	void
	 */
	protected function xbdtBulkTools()
	{
		try
		{
			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/1', 403, '' );
			}

			$form = new \IPS\Helpers\Form;

			/* Action selection — ordered by scope: compile < build < rebuild+download */
			$form->add( new \IPS\Helpers\Form\Radio( 'xbdt_action', 'compilejs', TRUE, array(
				'options' => array(
					'compilejs' => 'xbdt_action_compilejs',
					'build'     => 'xbdt_action_build',
					'download'  => 'xbdt_action_download',
				),
				'toggles' => array(
					'download' => array( 'xbdt_download_mode' ),
				),
			) ) );

			/* Download mode — only visible when download action is selected */
			$form->add( new \IPS\Helpers\Form\Radio( 'xbdt_download_mode', 'individual', FALSE, array(
				'options' => array(
					'individual' => 'xbdt_download_individual',
					'zip'        => 'xbdt_download_zip',
				),
			), NULL, NULL, NULL, 'xbdt_download_mode' ) );

			/* App selection — custom (non-IPS) apps first and pre-checked, IPS apps after and unchecked */
			$options = array();
			$defaultChecked = array();
			$ipsApps = \IPS\IPS::$ipsApps;

			/* Custom apps first */
			foreach ( \IPS\Application::applications() as $app )
			{
				if ( !\in_array( $app->directory, $ipsApps ) )
				{
					$options[ $app->directory ] = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $app->directory );
					$defaultChecked[] = $app->directory;
				}
			}
			/* Then IPS apps */
			foreach ( \IPS\Application::applications() as $app )
			{
				if ( \in_array( $app->directory, $ipsApps ) )
				{
					$options[ $app->directory ] = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $app->directory );
				}
			}

			$form->add( new \IPS\Helpers\Form\CheckboxSet( 'xbdt_apps', $defaultChecked, TRUE, array(
				'options' => $options,
			), function( $val ) {
				if ( empty( $val ) )
				{
					throw new \DomainException( 'xbdt_no_apps_selected' );
				}
			} ) );

			/* Handle form submission */
			if ( $values = $form->values() )
			{
				$_SESSION['xbdt_apps']          = $values['xbdt_apps'];
				$_SESSION['xbdt_download_mode'] = isset( $values['xbdt_download_mode'] ) ? $values['xbdt_download_mode'] : 'zip';

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtProcess&action=' . $values['xbdt_action'] )->csrf()
				);
			}

			\IPS\Output::i()->title  = \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_bulk_dev_tools' );
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
	 * Process selected apps via MultipleRedirect
	 *
	 * @return	void
	 */
	protected function xbdtProcess()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/2', 403, '' );
			}

			$action = \IPS\Request::i()->action;

			\IPS\Output::i()->output = new \IPS\Helpers\MultipleRedirect(
				\IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtProcess&action=' . $action )->csrf(),
				function( $data ) use ( $action )
				{
					/* First call — initialize from session */
					if ( $data === NULL )
					{
						$apps = isset( $_SESSION['xbdt_apps'] ) ? array_values( $_SESSION['xbdt_apps'] ) : array();
						if ( empty( $apps ) )
						{
							return NULL;
						}
						$data = array(
							'index'  => 0,
							'apps'   => $apps,
							'total'  => \count( $apps ),
							'errors' => array(),
						);
					}

					/* All done? */
					if ( $data['index'] >= $data['total'] )
					{
						/* Store errors in session for the results page */
						$_SESSION['xbdt_errors'] = $data['errors'];
						$_SESSION['xbdt_processed_apps'] = $data['apps'];
						return NULL;
					}

					$appKey  = $data['apps'][ $data['index'] ];
					$appName = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $appKey );
					$step    = ( $data['index'] + 1 ) . '/' . $data['total'];

					try
					{
						$application = \IPS\Application::load( $appKey );

						switch ( $action )
						{
							case 'build':
							case 'download':
								$application->build();
								break;

							case 'compilejs':
								$xml = \IPS\Output\Javascript::createXml( $appKey );
								if ( is_writable( \IPS\ROOT_PATH . '/applications/' . $appKey . '/data' ) )
								{
									\file_put_contents( \IPS\ROOT_PATH . '/applications/' . $appKey . '/data/javascript.xml', $xml->outputMemory() );
								}
								\IPS\Output\Javascript::compile( $appKey );
								if ( $appKey === 'core' )
								{
									\IPS\Output\Javascript::compile( 'global' );
								}
								break;
						}
					}
					catch ( \Exception $e )
					{
						$data['errors'][] = $appKey . ': ' . $e->getMessage();
					}

					$data['index']++;
					$pct = \intval( ( $data['index'] / $data['total'] ) * 100 );

					$label = ( $action === 'compilejs' )
						? \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_compiling_js', FALSE, array( 'sprintf' => array( $appName, $data['index'], $data['total'] ) ) )
						: \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_building', FALSE, array( 'sprintf' => array( $appName, $data['index'], $data['total'] ) ) );

					return array( $data, $label, $pct );
				},
				function() use ( $action )
				{
					if ( $action === 'download' )
					{
						\IPS\Output::i()->redirect(
							\IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtDownloadResults' )
						);
					}
					else
					{
						$errors = isset( $_SESSION['xbdt_errors'] ) ? $_SESSION['xbdt_errors'] : array();
						unset( $_SESSION['xbdt_errors'], $_SESSION['xbdt_apps'], $_SESSION['xbdt_processed_apps'] );

						if ( !empty( $errors ) )
						{
							\IPS\Output::i()->error( \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_errors_occurred' ) . '<br>' . implode( '<br>', array_map( 'htmlspecialchars', $errors ) ), '' );
						}

						\IPS\Output::i()->redirect(
							\IPS\Http\Url::internal( 'app=core&module=applications&controller=applications' ),
							'xbdt_process_complete'
						);
					}
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
	 * Download results page — shows ZIP and individual download options
	 *
	 * @return	void
	 */
	protected function xbdtDownloadResults()
	{
		try
		{
			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/3', 403, '' );
			}

			$apps   = isset( $_SESSION['xbdt_processed_apps'] ) ? $_SESSION['xbdt_processed_apps'] : array();
			$errors = isset( $_SESSION['xbdt_errors'] ) ? $_SESSION['xbdt_errors'] : array();
			$mode   = isset( $_SESSION['xbdt_download_mode'] ) ? $_SESSION['xbdt_download_mode'] : 'zip';

			if ( empty( $apps ) )
			{
				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=core&module=applications&controller=applications' ),
					'xbdt_no_apps_selected'
				);
			}

			/* Build the results HTML */
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
			$zipUrl = \IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtDownloadZip' )->csrf();
			$html .= '<p>' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_download_results_desc' ) . '</p>';
			$html .= '<div class="ipsButtonBar">';
			$html .= '<a href="' . $zipUrl . '" class="ipsButton ipsButton_primary ipsButton_medium"><i class="fa fa-file-archive-o"></i> &nbsp;' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_download_all_zip' ) . '</a>';
			$html .= '</div><br>';

			/* Individual download table */
			$html .= '<table class="ipsTable ipsTable_zebra">';
			$html .= '<thead><tr><th>Application</th><th>Version</th><th></th></tr></thead>';
			$html .= '<tbody>';

			foreach ( $apps as $appKey )
			{
				try
				{
					$application = \IPS\Application::load( $appKey );
					$appName     = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $appKey );
					$tarUrl      = \IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtDownloadTar&appKey=' . $appKey )->csrf();

					$html .= '<tr>';
					$html .= '<td>' . htmlspecialchars( $appName ) . '</td>';
					$html .= '<td>' . htmlspecialchars( $application->version ) . '</td>';
					$html .= '<td class="ipsTable_wrap"><a href="' . $tarUrl . '" class="ipsButton ipsButton_light ipsButton_verySmall"><i class="fa fa-download"></i> &nbsp;' . \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_download_tar' ) . '</a></td>';
					$html .= '</tr>';
				}
				catch ( \Exception $e )
				{
					continue;
				}
			}

			$html .= '</tbody></table>';

			/* If user chose individual mode, auto-start sequential downloads via JS */
			if ( $mode === 'individual' )
			{
				$html .= '<script type="text/javascript">';
				$html .= '(function() {';
				$html .= '  var urls = [';
				$first = true;
				foreach ( $apps as $appKey )
				{
					$tarUrl = \IPS\Http\Url::internal( 'app=core&module=applications&controller=applications&do=xbdtDownloadTar&appKey=' . $appKey )->csrf();
					if ( !$first ) { $html .= ','; }
					$html .= '"' . addslashes( $tarUrl ) . '"';
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

			\IPS\Output::i()->title  = \IPS\Member::loggedIn()->language()->addToStack( 'xbdt_download_results' );
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
	 * Download a single app as .tar
	 *
	 * @return	void
	 */
	protected function xbdtDownloadTar()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/4', 403, '' );
			}

			$appKey      = \IPS\Request::i()->appKey;
			$application = \IPS\Application::load( $appKey );
			$appName     = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $appKey );

			$pharPath = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/' . $appKey . '.tar';
			$download = new \PharData( $pharPath, 0, $appKey . '.tar', \Phar::TAR );
			$download->buildFromIterator( new \IPS\Application\BuilderIterator( $application ) );

			$output = \file_get_contents( $pharPath );

			unset( $download );
			\Phar::unlinkArchive( $pharPath );

			\IPS\Output::i()->sendOutput( $output, 200, 'application/tar', array(
				'Content-Disposition' => \IPS\Output::getContentDisposition( 'attachment', $appName . " {$application->version}.tar" )
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
	 * Download all selected apps as a single ZIP bundle
	 *
	 * @return	void
	 */
	protected function xbdtDownloadZip()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			if ( !\IPS\IN_DEV )
			{
				\IPS\Output::i()->error( 'not_in_dev', '2XBDT/5', 403, '' );
			}

			$apps = isset( $_SESSION['xbdt_processed_apps'] ) ? $_SESSION['xbdt_processed_apps'] : array();
			if ( empty( $apps ) )
			{
				\IPS\Output::i()->error( 'xbdt_no_apps_selected', '2XBDT/6', 400, '' );
			}

			$zipPath  = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/xbdt_bulk_download.zip';
			$tarPaths = array();

			$zip = new \ZipArchive();
			if ( $zip->open( $zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) !== TRUE )
			{
				\IPS\Output::i()->error( 'Could not create ZIP archive', '2XBDT/7', 500, '' );
			}

			foreach ( $apps as $appKey )
			{
				try
				{
					$application = \IPS\Application::load( $appKey );
					$appName     = \IPS\Member::loggedIn()->language()->addToStack( '__app_' . $appKey );

					$pharPath = str_replace( '\\', '/', rtrim( \IPS\TEMP_DIRECTORY, '/' ) ) . '/' . $appKey . '.tar';
					$phar     = new \PharData( $pharPath, 0, $appKey . '.tar', \Phar::TAR );
					$phar->buildFromIterator( new \IPS\Application\BuilderIterator( $application ) );

					unset( $phar );

					$tarFilename = $appName . " {$application->version}.tar";
					$zip->addFile( $pharPath, $tarFilename );
					$tarPaths[] = $pharPath;
				}
				catch ( \Exception $e )
				{
					\IPS\Log::log( $e, 'xbdt' );
					continue;
				}
			}

			$zip->close();

			$output = \file_get_contents( $zipPath );

			/* Cleanup temp files */
			@unlink( $zipPath );
			foreach ( $tarPaths as $path )
			{
				unset( $phar );
				try { \Phar::unlinkArchive( $path ); } catch ( \Exception $e ) {}
			}

			/* Clean up session */
			unset( $_SESSION['xbdt_apps'], $_SESSION['xbdt_processed_apps'], $_SESSION['xbdt_errors'], $_SESSION['xbdt_download_mode'] );

			\IPS\Output::i()->sendOutput( $output, 200, 'application/zip', array(
				'Content-Disposition' => \IPS\Output::getContentDisposition( 'attachment', 'IPS4-Apps-Bulk-Download.zip' )
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
}
