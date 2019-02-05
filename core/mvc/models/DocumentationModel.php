<?php

	class DocumentationModel extends BaseModel {
		private $routes;
		private $section_template = <<<HTML
			<div class="row" id="{{write_json_response}}">
				<div class="card">
					<div class="card-title" style="padding-left: 15px; padding-top: 10px;">
						<div class="row {{affichage}}">
							<div class="col s12">
								<h5 class="title">{{title}}</h5>
							</div>
						</div>
					</div>
					<div class="card-content">
						<div class="row">
							<div class="col s12">
								<p>
									{{describe}}
								</p>
							</div>
							<div class="col s10">
								<div class="row">
									<div class="col s12 api_url">
										<code><pre><b>{{http_method}} [domain]/api/index.php{{url}}</b></pre></code>	
									</div>
									<div class="col s12 api_url">
										 <code><pre><i>{{alias}}</i></pre></code>
									</div>
								</div>
							</div>
							<div class="col s2">
								<span data-badge-caption="" class="http-code-{{write_json_response}}"></span>
							</div>
							<div class="col s12">
								{{input_fields}}
							</div>
							<div class="col s12" style="max-height: 300px; overflow: auto;">
								<pre class="write_json_response {{write_json_response}}"><code></code></pre>
							</div>
						</div>
					</div>
				</div>
			</div>
HTML;

		/**
		 * @param $http_verb
		 * @param $url
		 * @param array $params
		 * @param null $alias
		 * @param string $title
		 * @param string $describe
		 * @param bool $request_active
		 * @return mixed
		 * @throws Exception
		 */
		private function get_section_template($http_verb, $url, $params = [], $alias = null, $title = '', $describe = '', $request_active = true) {
			$input_fields = '';
			foreach ($params as $param => $type) {
				if($type === 'string') {
					$type = 'text';
				}
				elseif ($type === 'int') {
					$type = 'number';
				}
				elseif ($type === 'bool' || $type === 'boolean') {
					$type = 'checkbox';
				}
				else {
					$type = 'text';
				}
				if($type === 'checkbox') {
					$input_fields .= '<div class="col s12 m6 l4">
	<div class="input-field">
		<p>
			<label>
				<input 	type="'.$type.'" value=1 
						class="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'" 
						id="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'-'.$param.'-'.$type.'"
						 placeholder="'.$param.'"/>
				<span>'.$param.'</span>
        	</label>
    	</p>
	</div>
</div>';
				}
				else {
					$input_fields .= '<div class="col s12 m6 l4">
	<div class="input-field">
		<label for="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'-'.$param.'-'.$type.'">'.$param.'</label>
		<input type="'.$type.'" class="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'" id="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'-'.$param.'-'.$type.'" placeholder="'.$param.'" />
	</div>
</div>';
				}
			}
			if($request_active) {
				$input_fields .= '<div class="col s12">
	<input 	type="button" class="btn orange" 
			data-url="/api/index.php'.$url.(!is_null($alias) ? '/'.$alias : '').'" 
			value="Envoyer" data-http_verb="'.$http_verb.'" 
			data-class="'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : '').'" />
</div>';
			}

			/** @var OsService $service_os */
			$service_os = $this->get_service('os');
			$describe = str_replace(' * ', '', $describe);
			$describe = str_replace($service_os->get_chariot_return(), '<br>', $describe);
			$describe = str_replace("\t", '', $describe);
			$describe = str_replace("*/", '', $describe);

			return str_replace(
				[
					'{{http_method}}',
					'{{url}}',
					'{{input_fields}}',
					'{{alias}}',
					'{{write_json_response}}',
					'{{title}}',
					'{{describe}}',
					'{{affichage}}'
				], [
					$http_verb,
					$url,
					$input_fields,
					(is_null($alias) ? '' : '[ALIAS '.$http_verb.' [domain]/api/index.php'.$url.'/'.$alias.']'),
					'write_json_response'.str_replace('/', '_', $url).(!is_null($alias) ? '_'.$alias : ''),
					$title,
					$describe,
					($title === '' ? 'hide' : 'show')
				], $this->section_template
			);
		}

		/**
		 * @throws ReflectionException
		 * @throws Exception
		 */
		private function generate_routes() {
			/** @var OsService $service_os */
			$service_os = $this->get_service('os');
			$retour = $service_os->get_chariot_return();
			$routes = [];
			foreach ($this->get_controllers() as $controller) {
				$class = $controller;
				$controller = ucfirst($controller).'Controller';
				if(is_file(__DIR__.'/../controllers/'.$controller.'.php')) {
					require_once __DIR__.'/../controllers/'.$controller.'.php';
					$ref     = new ReflectionClass($controller);
					$class_doc = $ref->getDocComment();
					$class_doc = str_replace('/**'.$retour, '', $class_doc);
					$class_doc = str_replace("\t", '', $class_doc);
					$class_doc = str_replace('*', '', $class_doc);
					$class_doc = str_replace($retour." */", '', $class_doc);
					$class_doc = str_replace("$retour*/", '', $class_doc);
					$class_doc = explode($retour, $class_doc);
					$class_in_doc = true;
					$request = true;
					foreach ($class_doc as $line) {
						preg_match('`@not_in_doc`', $line, $matches);
						if(!empty($matches)) {
							$class_in_doc = false;
							continue;
						}

						preg_match('`@not_request`', $line, $matches);
						if(!empty($matches)) {
							$request = false;
							continue;
						}
					}
					if(!$class_in_doc) {
						continue;
					}

					$methods = $ref->getMethods();
					foreach ($methods as $method) {
						if ($method->getName() !== $class && $method->isPublic() && $method->class !== Controller::class && $method->class !== Base::class) {
							$params = [];
							$alias = null;
							$http_verb = 'GET';
							$title = '';
							$describe = '';
							$not_in_doc = false;
							$doc = $method->getDocComment();
							$doc = str_replace('/**'.$retour, '', $doc);
							$doc = str_replace($retour."\t */", '', $doc);
							$doc = str_replace($retour."\t\t */", '', $doc);
							$doc = explode($retour, $doc);
							foreach ($doc as $line) {
								preg_match('`@param ([a-z]+) \$([A-Za-z0-9\_]+)`', $line, $matches);
								if(!empty($matches)) {
									$params[$matches[2]] = $matches[1];
								}
								preg_match('`@alias_method ([a-zA-Z\_]+)`', $line, $matches);
								if(!empty($matches)) {
									$alias = $matches[1];
									continue;
								}
								preg_match('`@http_verb ([a-zA-Z]+)`', $line, $matches);
								if(!empty($matches)) {
									$http_verb = strtoupper($matches[1]);
									continue;
								}
								preg_match('`@not_in_doc`', $line, $matches);
								if(!empty($matches)) {
									$not_in_doc = true;
									continue;
								}

								preg_match('`@title ([^\r\n\@]+)`', $line, $matches);
								if(!empty($matches)) {
									$title = $matches[1];
									continue;
								}

								preg_match('`@not_request`', $line, $matches);
								if(!empty($matches)) {
									$method_request = false;
									continue;
								}

								preg_match('`@describe ([^\@]+)`', $line, $matches);
								if(!empty($matches)) {
									$describe = $matches[1];
									continue;
								}

								if(!strstr($line, '@')) {
									$describe .= $service_os->get_chariot_return().$line;
								}
							}

							$infos = [
								'alias' => $alias,
								'params' => $params,
								'http_verb' => $http_verb,
								'in_doc' => !$not_in_doc,
								'title' => $title,
								'describe' => $describe,
								'request' => (isset($method_request) ? $method_request : $request),
							];

							$controller = strtolower(str_replace('Controller', '', $controller));
							if ($method->getName() === 'index') {
								$routes[$controller]['/'.$class] = $infos;
							}
							else {
								$routes[$controller]['/'.$class.'/'.$method->getName()] = $infos;
							}
						}
					}
				}
			}

			$this->routes = $routes;
		}

		private function get_nb_routes_to_show($controller) {
			$routes = $this->routes[$controller];
			$count = 0;
			foreach ($routes as $route) {
				if($route['in_doc']) {
					$count++;
				}
			}
			return $count;
		}

		/**
		 * @return string
		 * @throws ReflectionException
		 * @throws Exception
		 */
		public function get_doc_content() {
			/** @var SessionService $session_service */
			$session_service = $this->get_service('session');
			/** @var Micro_templatingService $templating_service */
			$templating_service = $this->get_service('micro_templating');
			$sections = '';
			$sidenav_controllers = '';
			$this->generate_routes();

			ksort($this->routes);

			foreach ($this->routes as $controller => $routes) {
				if($controller !== 'errors') {
					$sections .= '<div class="row page" id="'.$controller.'">
	<div class="col s12 center-align">
		<h4>'.ucfirst($controller).' routes</h4>
	</div>
	<div class="col s12">';
					if($this->get_nb_routes_to_show($controller) === 0) {
						$sections .= '<div class="row">
	<div class="col s12">
		<div class="card">
			<div class="card-title center-align" style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">
				<h5>Il n\'y a aucune route à afficher dans le controlleur `'.$controller.'`</h5>
			</div>
		</div>
	</div>
</div>';
					}
					else {
						foreach ($routes as $route => $detail) {
							if ($detail['in_doc']) {
								$sections .= $this->get_section_template($detail['http_verb'], $route, $detail['params'], $detail['alias'], $detail['title'], $detail['describe'], $detail['request']);
							}
						}
					}
					$sections .= '</div></div>';
				}
			}

			$controller_selected = $session_service->has_key('doc_page') ? $session_service->get('doc_page') : 'documentation';
			foreach ($this->get_controllers() as $controller) {
				if($controller !== 'errors') {
					$sidenav_controllers .= '<li '.($controller === $controller_selected ? 'class="active"' : '').'>
	<a href="#'.strtolower($controller).'" class="page-changer">'.ucfirst($controller).'</a>
</li>';
				}
			}
			$templating_service->set_path(__DIR__.'/../views/documentation');
			return $templating_service->display('developer', [
				'sections' => $sections,
				'sidenav_controllers' => $sidenav_controllers
			]);
		}

		public function get_connexion_content($error_message = null) {
			if(is_null($error_message)) {
				$error_message = '';
			}
			$color_class = $error_message === '' ? '' : 'red-text';
			$content = <<<HTML
	<DOCTYPE html>
	<html lang="fr">
		<head>
        	<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta charset="utf-8" />
			<title>Documentation Pizzygo API</title>
			<link rel="icon" href="/public/img/logo_pizzygo.png" />
			<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
			<link rel="stylesheet" href="/public/libs/materialize/css/materialize.min.css" />
			<script src="https://code.jquery.com/jquery-3.3.1.js"
			          integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
					  crossorigin="anonymous"></script>
			<script src="/public/libs/materialize/js/materialize.min.js"></script>
			<script>
				$(window).ready(() => {
					$('.sidenav').sidenav();
				});
			</script>
		</head>
		<body>
			<nav>
				<div class="nav-wrapper orange">
					<a href="#" class="brand-logo">
						<img src="/public/img/logo_pizzygo.png" style="padding-left: 10px;height: 65px;" alt="logo" />
					</a>
            		<a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
					<ul id="nav-mobile" class="right hide-on-med-and-down">
						<li class="active"><a href="/api/index.php/documentation/developer">Développeur</a></li>
                		<li><a href="/api/index.php/documentation/user">Utilisateur</a></li>
				  	</ul>
				  	<ul class="sidenav" id="mobile-demo">
						<li class="active"><a href="/api/index.php/documentation/developer">Développeur</a></li>
						<li><a href="/api/index.php/documentation/user">Utilisateur</a></li>
					</ul>
				</div>
			</nav>
			<header>
				<div class="container">
					<div class="col s12 center-align">
						<h1 class="title">Connexion</h1>
					</div>
				</div>
			</header>
			<main>
				<div class="container">
					<form method="post" action="/api/index.php/documentation">
						<div class="row">
							<div class="col s12 m6">
								<div class="input-field">
									<label for="email">Email</label>
									<input name="email" type="email" id="email" placeholder="Email" />
								</div>
							</div>
							<div class="col s12 m6">
								<div class="input-field">
									<label for="password">Password</label>
									<input name="password" type="password" id="password" placeholder="Password" />
								</div>
							</div>
							<div class="col s12 {$color_class}">{$error_message}</div>
							<div class="col s12 m4 offset-m4">
								<div class="btn-block center-align">
									<input type="submit" id="connexion" class="btn orange" value="Se connected" />
								</div>
							</div>
						</div>
					</form>
				</div>
			</main>
		</body>
	</html>
HTML;
			return $content;

		}

		/**
		 * @param UserEntity $user
		 * @throws Exception
		 */
		public function create_session(UserEntity $user) {
			/** @var SessionService $session_service */
			$session_service = $this->get_service('session');
			$session_service->set('doc_admin', $user->toArrayForJson());
		}

		/**
		 * @throws Exception
		 */
		public function delete_session() {
			/** @var SessionService $session_service */
			$session_service = $this->get_service('session');
			$session_service->remove('doc_admin');
			$session_service->remove('doc_page');
			return !$session_service->has_key('doc_admin');
		}

		/**
		 * @throws Exception
		 */
		public function get_user_doc_content() {
			/** @var Micro_templatingService $templating_service */
			$templating_service = $this->get_service('micro_templating');
			$templating_service->set_path(__DIR__.'/../views/documentation');
			return $templating_service->display('user');
		}
	}