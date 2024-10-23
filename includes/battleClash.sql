/*
USE master;
GO

-- Terminar las conexiones
ALTER DATABASE BattleClash
SET SINGLE_USER
WITH ROLLBACK IMMEDIATE;
GO

-- Eliminar la base de datos
DROP DATABASE BattleClash;
GO

*/
--Base de datos Tablas de BattleClash
-- Crear la base de datos
CREATE DATABASE BattleClash;
GO

USE BattleClash;
GO

CREATE TABLE Usuario (
    id_jugador INT PRIMARY KEY IDENTITY(1,1),   
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE, 
    contrasena VARBINARY(64) NOT NULL,
    correo VARCHAR(100) NOT NULL,               
    ranking INT DEFAULT 0                      
);
GO

CREATE TABLE Rol (
    id_rol INT PRIMARY KEY IDENTITY(1,1),       
    rol VARCHAR(10) CHECK (rol IN ('admin', 'jugador', 'bloqueado')),
    id_jugador INT,                             
    FOREIGN KEY (id_jugador) REFERENCES Usuario(id_jugador) 
);
GO

CREATE TABLE Carta (
    id_carta INT PRIMARY KEY IDENTITY(1,1),
    tipo_carta VARCHAR(50),
    nombre_carta VARCHAR(100) UNIQUE,
    foto NVARCHAR(255) NOT NULL,
    poder_ataque INT,
    poder_defensa INT
);
GO

CREATE TABLE Arena (
    id_arena INT PRIMARY KEY IDENTITY(1,1),
    nombre_arena VARCHAR(100) UNIQUE,
    tipo_arena VARCHAR(50),
    ranking_min INT,
    ranking_max INT
);
GO

CREATE TABLE Mazo (
    id_mazo INT PRIMARY KEY IDENTITY(1,1),
    id_jugador INT NOT NULL,
    nombre_mazo VARCHAR(50),
    FOREIGN KEY (id_jugador) REFERENCES Usuario(id_jugador) 
);
GO

CREATE TABLE AsignacionMazo (
    id_mazo INT NOT NULL,
    id_carta INT NOT NULL,
    PRIMARY KEY (id_mazo, id_carta),
    FOREIGN KEY (id_mazo) REFERENCES Mazo(id_mazo),
    FOREIGN KEY (id_carta) REFERENCES Carta(id_carta)
);
GO

CREATE TABLE Partida (
    id_partida INT PRIMARY KEY IDENTITY,
    id_jugador1 INT NOT NULL,
    id_jugador2 INT NOT NULL,
    id_mazo_jugador1 INT NOT NULL,
    id_mazo_jugador2 INT NOT NULL,
    id_arena INT NOT NULL,
    ganador INT,
    fecha_partida DATETIME NOT NULL,
    FOREIGN KEY (id_jugador1) REFERENCES Usuario(id_jugador), 
    FOREIGN KEY (id_jugador2) REFERENCES Usuario(id_jugador), 
    FOREIGN KEY (id_mazo_jugador1) REFERENCES Mazo(id_mazo),
    FOREIGN KEY (id_mazo_jugador2) REFERENCES Mazo(id_mazo),
    FOREIGN KEY (id_arena) REFERENCES Arena(id_arena)
);
GO

CREATE TABLE PartidaCarta (
    id_partida INT NOT NULL,
    id_carta_jugador1 INT,
    id_carta_jugador2 INT,
    PRIMARY KEY (id_partida, id_carta_jugador1, id_carta_jugador2),
    FOREIGN KEY (id_partida) REFERENCES Partida(id_partida),
    FOREIGN KEY (id_carta_jugador1) REFERENCES Carta(id_carta),
    FOREIGN KEY (id_carta_jugador2) REFERENCES Carta(id_carta)
);
GO

CREATE TABLE Copa (
    id_partida INT,
    id_jugador INT,
    copas_ganadas INT DEFAULT 3,
    PRIMARY KEY (id_partida, id_jugador),
    FOREIGN KEY (id_partida) REFERENCES Partida(id_partida),
    FOREIGN KEY (id_jugador) REFERENCES Usuario(id_jugador) 
);
GO

--SP: Stored Procedires
--Login
CREATE OR ALTER PROCEDURE sp_login
    @correo NVARCHAR(255),
    @contrasena NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @contrasenaBD VARBINARY(64);
    DECLARE @hashedContrasena VARBINARY(64);

    SELECT @contrasenaBD = contrasena
    FROM Usuario
    WHERE correo = @correo;

    IF @contrasenaBD IS NULL
    BEGIN
        RAISERROR('Usuario no encontrado.', 16, 1);
        RETURN;
    END

    -- Encriptar la contraseña 
    SET @hashedContrasena = HASHBYTES('SHA2_256', @contrasena);

    IF @hashedContrasena = @contrasenaBD
    BEGIN

        SELECT 
            u.id_jugador AS ID,
            u.correo AS email,
            u.nombre_usuario AS username,
            r.rol AS rol  
        FROM 
            Usuario u
            INNER JOIN Rol r ON u.id_jugador = r.id_jugador
        WHERE 
            u.correo = @correo;
    END
    ELSE
    BEGIN

        RAISERROR('Las credenciales no coinciden.', 16, 1);

    END
END;
GO

--Crear Usuario
CREATE PROCEDURE sp_registrar_jugador
    @nombre_usuario NVARCHAR(50),
    @correo NVARCHAR(100),
    @contrasena NVARCHAR(255),
    @rol NVARCHAR(10) 
AS
BEGIN
    SET NOCOUNT ON;

    IF @rol NOT IN ('admin', 'jugador')
    BEGIN
        RAISERROR('El rol debe ser "admin" o "jugador".', 16, 1);
        RETURN;
    END

    DECLARE @hashedContrasena VARBINARY(64);
    SET @hashedContrasena = HASHBYTES('SHA2_256', @contrasena);

    INSERT INTO Usuario (nombre_usuario, contrasena, correo)
    VALUES (@nombre_usuario, @hashedContrasena, @correo);

    IF @@ROWCOUNT = 0
    BEGIN
        RAISERROR('Error al registrar el usuario.', 16, 1);
        RETURN;
    END

    DECLARE @id_jugador INT;
    SET @id_jugador = SCOPE_IDENTITY();

    INSERT INTO Rol (rol, id_jugador)
    VALUES (@rol, @id_jugador);

    IF @@ROWCOUNT = 0
    BEGIN
        RAISERROR('Error al asignar el rol.', 16, 1);
        RETURN;
    END
END;
GO

--Listar
CREATE PROCEDURE sp_listar_usuario
    @criterio NVARCHAR(100) = NULL, 
    @rol NVARCHAR(10) = NULL 
AS
BEGIN 
    SELECT 
        u.id_jugador AS ID, 
        u.nombre_usuario AS Username, 
        u.ranking, 
        r.rol 
    FROM 
        Usuario u
    INNER JOIN
        Rol r ON u.id_jugador = r.id_rol
    WHERE
        (
            (@criterio IS NULL OR u.nombre_usuario LIKE '%' + @criterio + '%')
            OR (@criterio IS NULL OR CAST(u.ranking AS NVARCHAR) LIKE '%' + @criterio + '%')
        )
        AND 
        (@rol IS NULL OR r.rol LIKE '%' + @rol + '%')
END;
GO

--Leer información del usuario
CREATE PROCEDURE sp_leer_info_usuario
@id INT
AS
BEGIN
    SELECT 
        u.id_jugador, u.nombre_usuario, r.rol
    FROM 
        Usuario u 
    INNER JOIN 
        Rol r ON u.id_jugador = r.id_jugador
    WHERE 
        u.id_jugador = @id
END;
GO

--Actualizar
CREATE PROCEDURE sp_actualizar_usuario
@rol NVARCHAR(11),
@id INT 

AS
BEGIN

    UPDATE
        Rol
    SET
        rol = @rol
    WHERE
        id_jugador = @id

END;
GO

--Carta
--LEER
CREATE PROCEDURE sp_listar_carta
@criterio NVARCHAR(100) 
AS
BEGIN
	SELECT
		id_carta, tipo_carta, nombre_carta, foto, poder_ataque, poder_defensa
	FROM 
		Carta
	WHERE
		(@criterio IS NULL OR tipo_carta LIKE '%' + @criterio + '%')
		OR (@criterio IS NULL OR nombre_carta LIKE '%' + @criterio + '%')
		OR (@criterio IS NULL OR (ISNUMERIC(@criterio) = 1 AND poder_ataque = CAST(@criterio AS INT)))
		OR (@criterio IS NULL OR (ISNUMERIC(@criterio) = 1 AND poder_defensa = CAST(@criterio AS INT)))
END;
GO

--Crear
CREATE PROCEDURE sp_insertar_carta
@link NVARCHAR(255),
@nombre NVARCHAR(50),
@tipo NVARCHAR(50),
@poderAtaque INT,
@poderDefensa INT
AS
BEGIN
  
  INSERT INTO Carta(foto, nombre_carta, tipo_carta, poder_ataque, poder_defensa)
  VALUES (@link, @nombre, @tipo, @poderAtaque, @poderDefensa);
  
END;
GO

--Listar carta para actualizar:
CREATE PROCEDURE sp_leer_info_carta
@id INT
AS
BEGIN
    SELECT 
        id_carta, foto, nombre_carta, tipo_carta, poder_ataque,
		poder_defensa
    FROM 
        Carta 
    WHERE 
        id_carta = @id
END;
GO

--Actualizar
CREATE PROCEDURE sp_actualizar_carta
@id INT,
@foto NVARCHAR(255),
@nombre NVARCHAR(100),
@tipo NVARCHAR(100),
@poderAtaque INT,
@poderDefensa INT
AS
BEGIN
    UPDATE 
	    Carta
	SET 
	    foto = @foto, nombre_carta = @nombre, tipo_carta = @tipo,
		poder_ataque = @poderAtaque, poder_defensa = @poderDefensa
	WHERE 
	    id_carta = @id
END;
GO

--Este sp es para actulizar la carta sin la foto solo datos
--Actualizar
CREATE PROCEDURE sp_actualizar_carta_sin_foto
@id INT,
@nombre NVARCHAR(100),
@tipo NVARCHAR(100),
@poderAtaque INT,
@poderDefensa INT
AS
BEGIN
    UPDATE 
	    Carta
	SET 
	    nombre_carta = @nombre, tipo_carta = @tipo,
		poder_ataque = @poderAtaque, poder_defensa = @poderDefensa
	WHERE 
	    id_carta = @id
END;
GO

--Buscar usuario para restablecer 
CREATE PROCEDURE sp_buscar_usuario
@correo NVARCHAR(255)
AS
BEGIN

    SELECT
	    id_jugador, nombre_usuario, correo
	FROM
	    Usuario
	WHERE
	    correo = @correo
END;
GO

--Cambiar contraseña
CREATE PROCEDURE sp_cambiar_contrasena
    @contrasena NVARCHAR(255),
    @id INT
AS
BEGIN

    DECLARE @nuevaContrasena VARBINARY(64);

    SET @nuevaContrasena = HASHBYTES('SHA2_256', @contrasena);

    UPDATE Usuario
    SET contrasena = @nuevaContrasena
    WHERE id_jugador = @id;

END;
GO


--Llenar las tablas
EXEC sp_registrar_jugador 
    @nombre_usuario = 'Admin', 
    @correo = 'admin@battleclash.com', 
    @contrasena = 'Holaque@#2', 
    @rol = 'admin';
