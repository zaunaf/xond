<?php

namespace Xond\Auth;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    private $conn;
    private $app;
    
    public function __construct($app)
    {   
        $this->app = $app;
    }

    public function loadUserByUsername($username)
    {
        /*
        $stmt = $this->conn->executeQuery('SELECT * FROM users WHERE username = ?', array(strtolower($username)));
        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        
        $c = new \Criteria();
        $c->add(PenggunaPeer::USERNAME, $username);
        $user = PenggunaPeer::doSelectOne($c);
        if (!is_object($user)) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        */
        $config = $this->app['config'];
         
        //$objName = 'pengguna';
        //$appName = 'MyApp';
        //$colName = strtoupper("nama");

        $objName = $config['login_user_table'];
        $appName = $config['nama_singkat'];
        $colName = strtoupper($config['login_username_column']);
        
        $modelPeerName = "\\".$appName."\\Model\\".phpNamize($objName)."Peer";
        // return constant($modelPeerName."::".$colName);
        
        $c = new Criteria();
        $c->add(constant($modelPeerName."::".$colName), $username);
        $user = $modelPeerName::doSelectOne($c);
        
        return new User($user->getUsername(), $user->getPassword(), array('ROLE_USER'), true, true, true, true);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}