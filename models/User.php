<?php

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    public function createUser($nome, $email, $senha, $perfil) {
        $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)");
        return $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hashed_password,
            'perfil' => $perfil
        ]);
    }

    public function updateUser($id, $nome, $email, $perfil, $senha = null) {
        if ($senha) {
            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, senha = :senha WHERE id = :id");
            return $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'perfil' => $perfil,
                'senha' => $hashed_password,
                'id' => $id
            ]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil WHERE id = :id");
            return $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'perfil' => $perfil,
                'id' => $id
            ]);
        }
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);

        echo "DEBUG: Email recebido: " . htmlspecialchars($email) . "<br>";
        echo "DEBUG: Senha recebida (texto puro): " . htmlspecialchars($password) . "<br>";

        if ($user) {
            echo "DEBUG: Usuário encontrado no DB: " . htmlspecialchars($user['email']) . "<br>";
            echo "DEBUG: Hash da senha no DB: " . htmlspecialchars($user['senha']) . "<br>";

            if (password_verify($password, $user['senha'])) {
                echo "DEBUG: password_verify RETORNOU TRUE! Login bem-sucedido!<br>";
                return $user;
            } else {
                echo "DEBUG: password_verify RETORNOU FALSE! Senha não confere.<br>";
            }
        } else {
            echo "DEBUG: Usuário NÃO encontrado para o email: " . htmlspecialchars($email) . "<br>";
        }
        return false;
    }
}