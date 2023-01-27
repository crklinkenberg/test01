**Prerequisites:**

> + **XAMPP 7.1.33 Download and Install** ( https://udomain.dl.sourceforge.net/project/xampp/XAMPP%20Windows/7.1.33/xampp-windows-x64-7.1.33-1-VC14-installer.exe )
> + **PHP Ver. 7.1 - 7.2**
> + **Composer 1.6.3**
>> + If no previous version installed in the system then. **Download Latest Composer** First: https://getcomposer.org/Composer-Setup.exe
>> + Clone the repository in your system and then,
>> + **Next,** **Open Administrator Powershell:** and then run command: ```Set-ExecutionPolicy RemoteSigned```
>> + and then go to the location of the script in the cloned repository directory. **( repertoryDist/scripts/ )**
>> 
>> + then run, ```./composer_1.6.3.ps1``` inside the **Powershell**. To replace and to change the version to **1.6.3**.
>> + After Doing all steps Sucessfully, Now run command: ```Set-ExecutionPolicy Restricted```

**Installation:**

1. Clone the project

	git clone git@github.com:jay3000bc/repertoryDist.git


2. Go to the folder application using cd command on your cmd or terminal

	Run 'composer install' on your cmd or terminal

3. Rename the existing **.htaccess** file to **.htaccess-server** OR you can delete existing **.htaccess** file in root directory. And then rename **.htaccess-local** file to **.htaccess**

4. Configuration changes in the below mentioned locations:

	4.1:
	
		Location: repertoryDist\config\route.php
		$absoluteUrl = 'http://www.newrepertory.com/';
		$baseApiURL = 'http://www.newrepertory.com/symcom/api/public/v1/';
		Here change the above urls "http://www.newrepertory.com/" with your localhost url "http://localhost/<project-folder-name>/"
	
	4.2:
		
		Location: repertoryDist\config\dev-config.php
		Change the database configuration deatails with your details:
		$dbHost = 'localhost';
		$dbUsername = '<username>';
		$dbPassword = '<password>';
		$dbName = '<database-name>';
	
	4.3:
	
		Location: repertoryDist\comparenew\config.php
		Change the database configuration deatails with your DB details:
		$dbHost = 'localhost';
		$dbUsername = '<username>';
		$dbPassword = '<password>';
		$dbName = '<database-name>';
	
		$baseUrl = 'http://www.newrepertory.com/comparenew/';
		Change the $baseUrl "http://www.newrepertory.com/comparenew/" with your url "http://localhost/<project-folder-name>/comparenew/"
	
	4.4:
	
		Location: repertoryDist\compareold\config.php
		Change the database configuration deatails with your DB details:
		$dbHost = 'localhost';
		$dbUsername = '<username>';
		$dbPassword = '<password>';
		$dbName = '<database-name>';

		$baseUrl = 'http://www.newrepertory.com/compareold/';
		Change the $baseUrl "http://www.newrepertory.com/compareold/" with your url "http://localhost/<project-folder-name>/compareold/"
	
	4.5:
	
		Location: repertoryDist\dev\config.php
		Change the database configuration deatails with your DB details:
		$dbHost = 'localhost';
		$dbUsername = '<username>';
		$dbPassword = '<password>';
		$dbName = '<database-name>';

		$baseUrl = 'http://www.newrepertory.com/dev/';
		Change the $baseUrl "http://www.newrepertory.com/dev/" with your url "http://localhost/<project-folder-name>/dev/"
	
	
5. Now inside **project-folder-name/comparenew** directory rename the existing **check-if-comparison-table-exist.php** page to **check-if-comparison-table-exist-server.php** OR you can delete the existing **check-if-comparison-table-exist.php** page. And then rename **check-if-comparison-table-exist-local.php** page to **check-if-comparison-table-exist.php**

6. Go to location "http://localhost/project-folder-name/symcom/api" on your cmd or terminal

	Run 'composer install'

	6.1: 	Copy .env.example file to .env on current location("http://localhost/project-folder-name/symcom/api"). You can type copy 
		.env.example .env if using command prompt Windows or cp .env.example .env if using terminal, Ubuntu	

	6.2:	Now Open your .env file and change the database name (DB_DATABASE) to whatever you have, username (DB_USERNAME) and password (DB_PASSWORD) field correspond to your configuration.

		Location: http://localhost/<project-folder-name>/symcom/api/.env
		Change database details here 
		DB_DATABASE=<database-name-here>
		DB_USERNAME=<username>
		DB_PASSWORD=<password>
		
		Also add the below line
		API_PREFIX=v1
	
	6.3 	Now in on your cmd or terminal at this location "http://localhost/project-folder-name/symcom/api" run below command
		
		php artisan key:generate
	
	6.4:	Location: http://localhost/project-folder-name/symcom/api/config/constants.php
		Change the api_base_path with your path

		'api_base_path' => 'http://localhost/<project-folder-name>/symcom/api/public/',
	
	6.5:	To fulfill some of our custom requirements we need to do the following changes:
	
	6.5.1:	Location: http://localhost/project-folder-name/symcom/api/vendor/laravel/passport/src/Bridge/UserRepository.php
		Here in this script(UserRepository.php) we have to add the below mentioned function at the end inside the class. Just copy the below function and paste /add it in UserRepository.php

	
		/**
		 * Custom added
		 */
		public function getEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity, $provider)
		{
			$provider = config('auth.guards.'.$provider.'.provider');

			if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
				throw new RuntimeException('Unable to determine authentication model from configuration.');
			}

			if (method_exists($model, 'findForPassport')) {
				$user = (new $model)->findForPassport($username);
			} else {
				$user = (new $model)->where('email', $username)->first();
			}

			if (! $user) {
				return;
			} elseif (method_exists($user, 'validateForPassportPasswordGrant')) {
				if (! $user->validateForPassportPasswordGrant($password)) {
					return;
				}
			} elseif (! $this->hasher->check($password, $user->getAuthPassword())) {
				return;
			}

			return new User($user->getAuthIdentifier());
		}
		
	
	
		
	6.5.2:	Location: http://localhost/project-folder-name/symcom/api/vendor/league/oauth2-server/src/Grant/PasswordGrant.php
		In this script do the following things:

	i: Comment out the existing validateUser() function. like shown below:
	
		// protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
		// {
		//     $username = $this->getRequestParameter('username', $request);
		//     if (is_null($username)) {
		//         throw OAuthServerException::invalidRequest('username');
		//     }

		//     $password = $this->getRequestParameter('password', $request);
		//     if (is_null($password)) {
		//         throw OAuthServerException::invalidRequest('password');
		//     }

		//     $user = $this->userRepository->getUserEntityByUserCredentials(
		//         $username,
		//         $password,
		//         $this->getIdentifier(),
		//         $client
		//     );
		//     if ($user instanceof UserEntityInterface === false) {
		//         $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

		//         throw OAuthServerException::invalidCredentials();
		//     }

		//     return $user;
		// }


	ii: Now add the new validateUser() function code which is given below. Just copy the below function and add it in PasswordGrant.php inside the class:
		
		/**
		* Customised validateUser()
		* Default one is commented above
		**/
		protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
		{
			$username = $this->getRequestParameter('username', $request);
			if (is_null($username)) {
				throw OAuthServerException::invalidRequest('username');
			}

			$password = $this->getRequestParameter('password', $request);
			if (is_null($password)) {
				throw OAuthServerException::invalidRequest('password');
			}

			$provider = $this->getRequestParameter('provider', $request);
			if (is_null($provider)) {
				throw OAuthServerException::invalidRequest('provider');
			}

			$user = $this->userRepository->getEntityByUserCredentials(
				$username,
				$password,
				$this->getIdentifier(),
				$client,
				$provider
			);
			if ($user instanceof UserEntityInterface === false) {
				$this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

				throw OAuthServerException::invalidCredentials();
			}

			return $user;
		}
