<?php
/**
 *
 * @author Andres Duarte M.
 *
 */
require_once 'notes_wsdl.php';

function connection()
{
    $config_json = file_get_contents("config.json");
    $config = json_decode($config_json);
    
    $mysqli = new mysqli($config->hostname, $config->username, $config->password, $config->database, $config->port);
    
    if ($mysqli->connect_error) 
     die("Error de Conexion (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        
    return $mysqli;
}

function auth($user, $pass)
{
    $user = trim($user);
    $pass = trim($pass);

    $mysqli = connection();
    
    try
    {
        $query = "SELECT * FROM ws_users WHERE user = ? AND password = ?";
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('ss', $user, $pass);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $title, $body, $created, $last_update);

        if($stmt->num_rows === 1)
        {
            $stmt->close();
            $mysqli->close();
            return TRUE;
        }
        $stmt->close();
        $mysqli->close();
        return FALSE;
    }
    catch(exception $e)
    {
        $stmt->close();
        $mysqli->close();
        return FALSE;
    }
}

function read($ws_user, $ws_pass, $id = NULL)
{
    if( ! auth($ws_user, $ws_pass))
    {
        return array('Success' => FALSE, 'Message' => 'Usuario o Clave incorrecta.', 'Rows' => 0, 'List' => array());
    }

    $mysqli = connection();

    if( ! empty($id))
    {
        $id = trim($id);

        try 
        {
            $query = "SELECT * FROM notes WHERE id = ?";
            $stmt = $mysqli->stmt_init();
            $stmt->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $title, $body, $created, $last_update);

            $num_rows = $stmt->num_rows;

            if($num_rows === 1)
            {
                $stmt->fetch();

                $list[] = array(
                    'id'          => $id,
                    'title'       => (string) $title,
                    'body'        => (string) $body,
                    'created'     => (string) $created, 
                    'last_update' => (string) $last_update
                );
                $stmt->close();
                $mysqli->close();

                return array(
                    'Success' => TRUE,
                    'Message' => '',
                    'Rows'    => $num_rows,
                    'List'    => $list
                );
            }
        }
        catch(exception $e)
        {
            return array('Success' => FALSE, 'Message' => $e->getMessage(), 'Rows' => 0, 'List' => array());
        }
    }
    else
    {
        try
        {
            $query = "SELECT * FROM notes";
            $result = $mysqli->query($query);
            $num_rows = $result->num_rows;

            if($num_rows > 0)
            {
                while ($note = $result->fetch_object()) 
                {
                    $list[] = array(
                        'id'          => $note->id,
                        'title'       => (string) $note->title,
                        'body'        => (string) $note->body,
                        'created'     => (string) $note->created, 
                        'last_update' => (string) $note->last_update
                    );
                }

                $result->close();
                $mysqli->close();

                return array(
                    'Success' => TRUE,
                    'Message' => '',
                    'Rows'    => $num_rows,
                    'List'    => $list
                );
            }
        }
        catch(exception $e)
        {
            return array('Success' => FALSE, 'Message' => $e->getMessage(), 'Rows' => 0, 'List' => array());
        }
    }
    return array(
        'Success' => FALSE,
        'Message' => 'No se han encontrado resultados.',
        'Rows'    => 0,
        'List'    => array()
    );
}

function create($ws_user, $ws_pass, $title, $body)
{
    if( ! auth($ws_user, $ws_pass))
    {
        return array('Success' => FALSE, 'Message' => 'Usuario o Clave incorrecta.', 'Rows' => 0, 'List' => array());
    }

    $title = trim($title);
    $body  = trim($body);

    $mysqli = connection();

    try 
    {
        $query = "INSERT IGNORE INTO notes (title, body, created) VALUES (?, ?, NOW())";
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('ss', $title, $body);
        $stmt->execute();

        if($mysqli->affected_rows === 1)
        {
            $stmt->close();
            $mysqli->close();
            return array('Success' => TRUE, 'Message' => 'Nota agregada.');
        }
        return array('Success' => FALSE, 'Message' => 'Hubo un error.');
    } 
    catch (exception $e) 
    {
        return array('Success' => FALSE, 'Message' => $e->getMessage());
    }
}

function update($ws_user, $ws_pass, $id, $title, $body)
{
    if( ! auth($ws_user, $ws_pass))
    {
        return array('Success' => FALSE, 'Message' => 'Usuario o Clave incorrecta.');
    }

    if( ! $id || ! $title || ! $body)
    {
        return array('Success' => FALSE, 'Message' => 'Faltan datos.');
    }

    $id    = trim($id);
    $title = trim($title);
    $body  = trim($body);

    $mysqli = connection();

    try 
    {
        $query = "UPDATE notes SET title = ?, body = ?, last_update = NOW() WHERE id = ?";
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('ssi', $title, $body, $id);
        $stmt->execute();

        if($mysqli->affected_rows === 1)
        {
            $stmt->close();
            $mysqli->close();
            return array('Success' => TRUE, 'Message' => 'Nota actualizada.');
        }
        return array('Success' => FALSE, 'Message' => 'Hubo un error.');
    }
    catch (exception $e) 
    {
        return array('Success' => FALSE, 'Message' => $e->getMessage());
    }
}

function delete($ws_user, $ws_pass, $id)
{
    if( ! auth($ws_user, $ws_pass))
    {
        return array('Success' => FALSE, 'Message' => 'Usuario o Clave incorrecta.');
    }

    if( ! $id)
    {
        return array('Success' => FALSE, 'Message' => 'Falta el id.');
    }

    $id = trim($id);

    $mysqli = connection();

    try 
    {
        $query = "DELETE FROM notes WHERE id = ?";
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if($mysqli->affected_rows === 1)
        {
            $stmt->close();
            $mysqli->close();
            return array('Success' => TRUE, 'Message' => 'Nota eliminada.');
        }
        return array('Success' => FALSE, 'Message' => 'Hubo un error.');
    }
    catch (exception $e) 
    {
        return array('Success' => FALSE, 'Message' => $e->getMessage());
    }
}
?>
