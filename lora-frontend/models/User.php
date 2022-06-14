<?php
require_once __DIR__ . '/../utilities/db.php';
require_once __DIR__ . '/../utilities/UUID.php';

class User implements JsonSerializable {
    protected $id;
    protected $linkblue;
    protected $role;

    public function __construct() {
        $this->id = UUID::v4();
    }

    public static function create($linkblue, $role): ?User {
        if (empty($linkblue))
            throw new Exception('You must provide a linkblue to create a user');
        if (is_null($role))
            throw new Exception('You must provide the access rights to create a user');
        $instance = new self();
        $instance->setLinkblue($linkblue);
        $instance->setRole($role);
        return $instance->save();
    }

    public static function delete($id): bool {
        if (empty($id) || is_null($id))
            throw new Exception('ID for user not found.');
        $instance = new self();
        $status = $instance->deleteFromId($id);
        return $status;
    }

    public static function update($id, $role): User {
        if (empty($id))
            throw new Exception('You must provide an id to update a user');
        $instance = User::withId($id);
        if (is_null($instance))
            throw new Exception("No user found with id [{$id}]");
        $instance->setRole($role);
        return $instance->save();
    }

    public static function all(): array {
        $users = [];
        $query = "SELECT * FROM users";
        $stmt = DB::run($query);
        while ($row = $stmt->fetch(PDO::FETCH_LAZY))
            array_push($users, User::withRow($row));
        return $users;
    }

    public static function countForDatatable(): int {
        $stmt = DB::run("SELECT count(id) FROM users");
        $count = $stmt->fetchColumn();
        return $count;
    }

    public static function countFilteredForDatatable(string $filter): int {
        $query = "SELECT count(id) FROM users";
        if (!is_null($filter) && strlen($filter) > 0) {
            $filter = "%{$filter}%";
            $query .= " WHERE (linkblue LIKE :filter)";
        }
        $stmt = DB::prepare($query);
        if (!is_null($filter) && strlen($filter) > 0) {
            $stmt->bindParam('filter', $filter);
        }
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count;
    }

    public static function listForDatatable(int $start, int $length, string $order_by, string $order_dir, string $filter): array {
        $matches = [];
        $query = "SELECT * FROM users";
        $prune = '';
        if ($length > 0)
            $prune .= " OFFSET {$start} ROWS FETCH NEXT {$length} ROWS ONLY";
        if (is_null($order_by) || $order_by == '' || $order_by == '0')
            $order_by = 'linkblue';
        if (is_null($order_dir) || $order_dir == '')
            $order_dir = 'desc';
        if (!is_null($filter) && strlen($filter) > 0) {
            $filter = '%' . $filter . '%';
            $query .= " WHERE (linkblue LIKE :filter)";
        }
        $query .= " ORDER BY {$order_by} {$order_dir}{$prune}";
        $stmt = DB::prepare($query);
        if (!is_null($filter) && strlen($filter) > 0) {
            $stmt->bindValue('filter', $filter);
        }
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_LAZY))
            array_push($matches, User::withRow($row));
        return $matches;
    }

    public static function withId( $id ): ?User {
        try {
            $instance = new self();
            $instance->loadById($id);
            return $instance;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function withLinkblue( $linkblue ): ?User {
        try {
            $instance = new self();
            $instance->loadByLinkblue($linkblue);
            return $instance;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function getAllRoles(): array {
        $roles = [];
        $query = "SELECT * FROM user_roles";
        try{
            $stmt = DB::run($query);
            while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                array_push($roles, array($row['id'], $row['role_name']));
        } catch (PDOException $e){ 
            return null;
        }
        return $roles;
    }

    public static function withRow( $row ): User {
        $instance = new self();
        $instance->fill( $row );
        return $instance;
    }

    protected function loadById( $id ): void {
        $stmt = DB::run(
            "SELECT * FROM users WHERE id = :id ORDER BY id OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY",
            ['id' => $id]
        );
        $row = $stmt->fetch(PDO::FETCH_LAZY);
        if ($row <> null)
            $this->fill( $row );
        else
            throw new PDOException("User with id [{$id}] not found");
    }

    protected function loadByLinkblue( $linkblue ): void {
        $stmt = DB::run(
            "SELECT * FROM users WHERE linkblue = :linkblue ORDER BY id OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY",
            ['linkblue' => $linkblue]
        );
        $row = $stmt->fetch(PDO::FETCH_LAZY);
        if ($row <> null)
            $this->fill( $row );
        else
            throw new PDOException("User with linkblue [{$linkblue}] not found");
    }

    protected function fill( $row ): void {
        $this->id = $row['id'];
        $this->linkblue = $row['linkblue'];
        $this->role = $row['role'];
    }

    protected function save(): ?User {
        $exists = User::withId($this->getId());
        if (is_null($exists)) {
            $insert = "INSERT INTO users (id, linkblue, role) VALUES (?,?,?)";
            DB::run($insert, [$this->getId(), $this->getLinkblue(), $this->getRole()]);
        } else {
            $update = "UPDATE users SET linkblue=?, role=? WHERE id=?";
            DB::run($update, [$this->getLinkblue(), $this->getRole(), $this->getId()]);
        }
        return User::withId($this->getId());
    }

    protected function deleteFromId( $id ): bool {
        $stmt = DB::prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam('id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) 
            return true;
        else
            return false;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLinkblue(): string {
        return $this->linkblue;
    }

    /**
     * @param string $linkblue
     */
    public function setLinkblue(string $linkblue): void {
        $this->linkblue = $linkblue;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool {
        if ($this->role == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function getRole(): int {
        return intval($this->role);
    }


    /**
     * @param int role
     */
    public function setRole(int $role): void {
        $this->role = $role;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): array {
        return [
            'id'        => $this->getId(),
            'linkblue'  => $this->getLinkblue(),
            'role'      => $this->getRole(),
        ];
    }
}