<?php

	/**
	 * Class DocumentationController
	 * @not_request
	 */
	class DocumentationController extends Controller {

		/** @var DocumentationModel $model */
		public $model;

		/**
		 * @title DOCUMENTATION
		 * @describe Injection de dépendences disponible :
		 *  - Par propriété grace à la PHPDoc de la propriété
		 *  - Par paramètres de méthode dans les controlleurs en definissant le type voulu devant la variable.
		 * @return Response
		 * @throws Exception
		 */
		public function index(SessionService $session_service = null, HttpService $http_service = null, UserDao $user_dao = null): HtmlResponse {
			return $this->developer($http_service, $user_dao, $session_service);
		}

		/**
		 * @return Response
		 * @throws ReflectionException
		 * @throws Exception
		 */
		public function developer(HttpService $http_service, UserDao $user_dao, SessionService $session_service): HtmlResponse {
			if($http_service->post('email') && $http_service->post('password')) {
				/** @var UserEntity|bool $user */
				$user = $user_dao->getByEmailAndPassword(
					$this->http_service->post('email'),
					sha1(sha1($this->http_service->post('password')))
				);
				if($user && $user->toArrayForJson()['roles'][0]['role'] === RoleEntity::ADMIN) {
					$this->model->create_session($user);
				}
				else {
					$object = $this->model->get_connexion_content('Votre compte n\'à pas les droits administrateurs !!');
					return $this->get_response($object, Response::HTML);
				}
			}
			$object = $session_service->has_key('doc_admin') ? $this->model->get_doc_content() : $this->model->get_connexion_content();

			return $this->get_response($object, Response::HTML);
		}

		/**
		 * @return Response
		 * @throws Exception
		 */
		public function user(): HtmlResponse {
			return $this->get_response($this->model->get_user_doc_content(), Response::HTML);
		}

		/**
		 * @return Response|HtmlResponse
		 * @throws Exception
		 */
		public function disconnect(): HtmlResponse {
			if($this->model->delete_session()) {
				header('location: /api/index.php/documentation');
			}
			return $this->get_response('', Response::HTML);
		}

		/**
		 * @param string $page_id
		 * @no_request
		 * @return Response
		 * @throws Exception
		 */
		public function change_page(SessionService $sessionService): Response {
			if($this->get('page_id')) {
				$sessionService->set('doc_page', $this->get('page_id'));
			}
			return $this->get_response('', Response::HTML);
		}
	}