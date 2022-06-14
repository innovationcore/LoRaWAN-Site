<?php

require_once __DIR__ . '/../utilities/db.php';
require_once __DIR__ . '/User.php';

class UserSession implements JsonSerializable {
    protected $session_id;
    protected $user;
    protected $last_seen;
    protected $remember_me;

    public function __construct() { }

    public static function create(string $session_id, User $user, bool $remember_me): UserSession {
        $instance = new self();
        $instance->setSessionId($session_id);
        $instance->setUser($user);
        $instance->setRememberMe($remember_me);
        $instance = $instance->save();
        return $instance;
    }

    /*public static function all(): array {
        $sessions = [];
        $query = "SELECT * FROM user_sessions";
        $stmt = DB::run($query);
        while ($row = $stmt->fetch(PDO::FETCH_LAZY))
            array_push($sessions, UserSession::withRow($row));
        return $sessions;
    }*/

    public static function withSessionID($id): ?UserSession {
        try {
            $instance = new self();
            $instance->loadBySessionID($id);
            if (!is_null($instance->last_seen)) {
                global $config;
                $timeout = (isset($config['sessions']) &&
                    isset($config['sessions']['timeout']) &&
                    is_int($config['sessions']['timeout']))
                    ? $config['sessions']['timeout'] : 7200;
                $now = new DateTime();
                if (($now->getTimestamp() - $instance->last_seen->getTimestamp()) > $timeout) {
                    self::delete($instance->session_id);
                    $_SESSION['LOGIN_ERROR'] = "You have been logged out due to inactivity";
                    $instance = null;
                }
            }
            return $instance;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function withRow($row): UserSession {
        $instance = new self();
        $instance->fill($row);
        return $instance;
    }

    public static function delete($session_id): void {
        $delete = "DELETE FROM user_sessions WHERE session_id = :session_id";
        DB::run($delete, ['session_id' => $session_id]);
    }

    protected function loadBySessionID($session_id): void {
        $stmt = DB::run(
            "SELECT * FROM user_sessions WHERE session_id = :session_id ORDER BY session_id OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY",
            ['session_id' => $session_id]
        );
        $row = $stmt->fetch(PDO::FETCH_LAZY);
        if ($row <> null) {
            $this->fill($row);
            $this->seen();
        } else
            throw new PDOException("Session with id [{$session_id}] not found");
    }

    protected function fill($row): void {
        $this->session_id = $row['session_id'];
        $this->user = User::withId($row['user_id']);
        try {
            $this->last_seen = new DateTime($row['last_seen']);
        } catch (Exception $e) {
            error_log("UserSession error setting last_seen [${row['last_seen']}]: " . $e->getMessage());
            $this->last_seen = new DateTime();
        }
        $this->remember_me = $row['remember_me'];
    }

    protected function save(): ?UserSession {
        $exists = UserSession::withSessionID($this->getSessionId());
        if (is_null($exists)) {
            $insert = "INSERT INTO user_sessions (session_id, user_id, remember_me) VALUES (?,?,?)";
            DB::run($insert, [$this->getSessionId(), $this->getUser()->getId(), $this->getRememberMe()]);
        } else {
            $update = "UPDATE user_sessions SET user_id=?, last_seen=GETDATE(), remember_me=? WHERE session_id=?";
            DB::run($update, [$this->getUser()->getId(), $this->getRememberMe(), $this->getSessionId()]);
        }
        return UserSession::withSessionID($this->getSessionId());
    }

    protected function seen(): void {
        $update = "UPDATE user_sessions SET last_seen=GETDATE() WHERE session_id=?";
        DB::run($update, [$this->getSessionId()]);
    }

    /**
     * @return string
     */
    public function getSessionId(): string {
        return $this->session_id;
    }
    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id): void {
        $this->session_id = $session_id;
    }

    /**
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }
    /**
     * @param User $user
     */
    public function setUser(User $user): void {
        $this->user = $user;
    }

    /**
     * @return DateTime
     */
    public function getLastSeen(): DateTime {
        return $this->last_seen;
    }

    /**
     * @return mixed
     */
    public function getRememberMe() {
        return $this->remember_me;
    }
    /**
     * @param mixed $remember_me
     */
    public function setRememberMe($remember_me): void {
        $this->remember_me = $remember_me;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): array {
        return [
            'session_id'    => $this->getSessionId(),
            'user'          => (!is_null($this->getUser())) ? $this->getUser()->jsonSerialize() : null,
            'last_seen'     => $this->getLastSeen()->getTimestamp(),
            'remember_me'   => $this->getRememberMe(),
        ];
    }
}