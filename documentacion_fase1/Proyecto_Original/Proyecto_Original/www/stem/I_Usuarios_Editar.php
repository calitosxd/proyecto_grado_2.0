<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $roles = $_POST['rol'];
    $permisos = $_POST['permiso'];
} else {
    // Si por algún motivo, no llegan datos, las variables se declaran vacías.
    $roles = null;
    $permisos = null;
}
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $roles . "<br>";
//echo "Permisos: " . $permisos;
?>

<!--Concatenador con las partes Head-Heater-Aside. -->
<?php 
    require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
    include('LOGICA/L_Funciones.php');
    error_reporting(E_ERROR |  E_PARSE);
    /* Se recibe y verifica que la variable "identificación" haya llegado por el metodo GET.*/
    if (isset($_POST['id_usuario'])) 
    {
        /*Se filtra y limpia el valor de la variable "identificacion" que ha llegado por el metodo GET*/
        $id_usuario = filtrado($_POST['id_usuario']);
        /* Se llama la funcion que permite buscar en la base de datos el registro de una persona.*/
        $datos = Funcion_Buscar_Usuario($id_usuario);
    }
    else
    {
        echo("El número de identificación no concuerda con el registro visto previamente.");
    }
?>
    <section>
        <div class="Section-css">
            <center>
            <div class="form">
                <h2>Editar Persona con Identificación Número:</h2>
                <?php
                    /*Imprime la identificación que se esta buscando.*/
                    echo ("<h3 style ='color: blue;'>[".$id_usuario."]</h3></br>");
                ?>
                <!-- Iniciamos un formulario, con datos precargados del "Usuario" que se busco previamente
                 donde solo se permite modificar informacion básica de éste. -->
                <form action="LOGICA/L_Usuarios_Editar.php" method="POST">
                <!-- Variables ocultas para enviar junto con el formulario -->
                <input type="hidden" name="roles" value="<?php echo $roles; ?>">
                <input type="hidden" name="permisos" value="<?php echo $permisos; ?>">
                <?php 
                foreach($datos as $dato) {
                // El dato_usuario[2], es el arreglo donde en el campo "2", contiene la información de la contraseña, la cual se encuentra encriptada, se realiza el proceso de desencriptación para ser mostrada.
                $contrasena = $dato[1];
                $contrasena_desencriptada = Funcion_Contrasena_desencriptar($contrasena);
                ?>
                <table>      
                    <th>Identificación</th>
                    <th>Nombre</th>
                    <!--<th colspan="2">Apellido</th>--> 
                    <th>Apellido</th>
                    <th>Edad</th>               
                    <tr>
                        <td><input style="text-align:center; color: red;" name="id" type="text" maxlength="10" required="Ingresar Numero de Identificacion." value ="<?php echo $dato[0];?>" readonly=""><br/></td>
                        <td><input style="text-align:center; color: red;" name="nombre" type="text" maxlength="15" required="Ingresar Nombre." value ="<?php echo $dato[2]; ?>" readonly=""><br/></td>
                        <td ><input style="text-align:center; color: red;" name="apellido" type="text" maxlength="20" required="Ingresar Aprellido." value ="<?php echo $dato[3]; ?>" readonly=""><br/></td>
                        <td><input style="text-align:center; color: red;" name="edad" type="text" maxlength="2" required="Ingresar Edad." value ="<?php echo $dato[4]; ?>" readonly=""><br/></td>
                    </tr>
                    <th>Género</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    </tr>  
                
                        <td><input style="text-align:center; color: red;" name="genero" type="text" required="Ingresar el genero de la persona." value ="<?php echo $dato[5]; ?>" readonly=""><br/></td>
                        <td><input style="text-align:center; color: green;" name="telefono" type="text" maxlength="10" required="Ingresar Número Telefonico." value ="<?php echo $dato[6]; ?>"><br/> </td>
                        <td><input style="text-align:center; color: green;" name="direccion" type="text" maxlength="25" required="Ingresar Dirección." value ="<?php echo $dato[7]; ?>"><br/></td>
                        <td><input style="text-align:center; color: green;" name="ciudad" type="text" maxlength="25" required="Ingresar Ciudad." value ="<?php echo $dato[8]; ?>"><br/></td>
                    </tr>
                </table>
                </br>
                <table>              
                    <th>Universidad</th>
                    <th>Programa</th>                    
                    <th colspan="2">Email</th>                                                                     
                    <tr>
                    <td><input style="text-align:center; color: green;" name="universidad" type="text" maxlength="25" required="Ingresar nombre de la entidad educativa del usuario." value ="<?php echo $dato[9]; ?>"><br/></td>                          
         
                        <td><input style="text-align:center; color: green;" name="programa" type="text" required="Ingresar el programa academico que cursa el usuario." value ="<?php echo $dato[10]; ?>"><br/></td>                           
                        <td colspan="2"><input style="text-align:center; color: green;" name="email" type="text" maxlength="45" required="Ingresar Email." value ="<?php echo $dato[11]; ?>"><br/></td>    
                    </tr>     
                    <th>Rol</th>
                    <th>Contraseña</th>
                    <th>Fecha Registro</th>
                    <th>Estado</th>
                    <tr>
                    <td>
                        <!--Se traduce el numero del Rol, con su significado en palabras.-->
                        <?php
                            if ($dato[15] == 0) 
                            {
                                $rolPalabras="Super Admin";
                            
                            }
                            elseif ($dato[15] == 1) 
                                {
                                    $rolPalabras="Administrativo";
                                
                                }
                            elseif ($dato[15] == 2) 
                                {
                                    $rolPalabras="Docente";
                                
                                }
                            elseif ($dato[15] == 3) 
                                {
                                    $rolPalabras="Estudiante";
                                
                                }
                            elseif ($dato[15] == 4) 
                                {
                                    $rolPalabras="Invitado";
                                
                                }

                            
                            if ($rol != 0)
                            {
                                if ($dato[15] == 0)
                                {
                                ?>
                                    <select name="rol" style="text-align:center; color: red;" required="Ingresar el rol de la persona.">
                                        <option value="<?php echo $rolPalabras; ?>" required="Seleccione una Opción."><?php echo $rolPalabras; ?></option> 
                                        <option disabled="" value="Administrativo" required="Seleccione una Opción.">Administrativo</option>
                                        <option disabled="" value="Docente" required="Seleccione una Opción.">Docente</option>
                                        <option disabled="" value="Estudiante" required="Seleccione una Opción.">Estudiante</option>
                                        <option disabled="" value="Invitado" required="Seleccione una Opción.">Invitado</option>
                                    </select> 
                                <?php
                                }
                                elseif($dato[15] != 0)
                                {
                                ?>
                                    <select name="rol" style="text-align:center; color: green;" required="Ingresar el rol de la persona.">
                                        <option value="<?php echo $rolPalabras; ?>" required="Seleccione una Opción."><?php echo $rolPalabras; ?></option> 
                                        <option value="Administrativo" required="Seleccione una Opción.">Administrativo</option>
                                        <option value="Docente" required="Seleccione una Opción.">Docente</option>
                                        <option value="Estudiante" required="Seleccione una Opción.">Estudiante</option>
                                        <option value="Invitado" required="Seleccione una Opción.">Invitado</option>
                                    </select> 
                                <?php
                                }
                            }

                            if ($rol == 0)
                            {
                                ?>
                                <select name="rol" style="text-align:center; color: green;" required="Ingresar el rol de la persona.">
                                    <option value="<?php echo $rolPalabras; ?>" required="Seleccione una Opción."><?php echo $rolPalabras; ?></option> 
                                    <option value="Administrativo" required="Seleccione una Opción.">Administrativo</option>
                                    <option value="Docente" required="Seleccione una Opción.">Docente</option>
                                    <option value="Estudiante" required="Seleccione una Opción.">Estudiante</option>
                                    <option value="Invitado" required="Seleccione una Opción.">Invitado</option>
                                    <option value="Super Admin" required="Seleccione una Opción.">Super Admin</option>
                                </select>  
                                <?php    
                            }
                        ?> 
                        </td>

                        <?php   
                        if ($rol != 0)
                            {
                                if ($dato[15] == 0)
                                {
                                ?>
                                    <td><input disabled="" style="text-align:center; color: red;" name="contrasena" type="text" required="Ingresar la constraseña de la persona." value ="<?php echo $contrasena_desencriptada; ?>"><br/></td>  
                                <?php
                                }
                                elseif($dato[15] != 0)
                                {
                                ?>
                                    <td><input style="text-align:center; color: green;" name="contrasena" type="text" required="Ingresar la constraseña de la persona." value ="<?php echo $contrasena_desencriptada; ?>"><br/></td>  
   
                                <?php
                                }
                            }

                            if ($rol == 0)
                            {
                                ?>
                                    <td><input style="text-align:center; color: green;" name="contrasena" type="text" required="Ingresar la constraseña de la persona." value ="<?php echo $contrasena_desencriptada; ?>"><br/></td> 
                                <?php    
                            }
                        ?> 
                        
                    <!-- Fecha de registro de un usuario. -->
                    <td><input style="text-align:center; color: red;" name="fecha_registro" type="date" class="fecha" value ="<?php echo $dato[12]; ?>" readonly=""><br/></td>
                    <!-- El estado de un usuario es: [(0) = Inactivos y (1) = Activo] -->
                    
                    
                    <?php 
                    if ($rol != 0)
                        {
                        if ($dato[15] == 0)
                            {
                            if ($dato[14] == 1) 
                                {
                    ?>
                                <td>
                                <select name="estado" style="text-align:center; color: red;" required="Ingresar el estado de la persona.">
                                    <option value="1" selected="">Activo</option>
                                    <!-- <option value="1" required="Seleccione una Opción.">Activo</option> -->
                                    <option disabled="" value="0" required="Seleccione una Opción.">Inactivo</option>
                                </select>
                                </td> 
                    <?php  
                                }
                            elseif ($dato[14] == 0) 
                                {
                    ?>
                                <td>
                                <select name="estado" style="text-align:center; color: black" required="Ingresar el estado de la persona.">
                                    <option disabled="" value="0" selected="">Inactivo</option>
                                    <option disabled="" value="1" required="Seleccione una Opción.">Activo</option>
                                    <!-- <option value="0" required="Seleccione una Opción.">Inactivo</option> -->
                                </select>
                                </td>    
                    <?php    
                                }
                            }  
                        elseif($dato[15] != 0)
                            {
                            if ($dato[14] == 1) 
                                {
                    ?>
                                <td>
                                <select name="estado" style="text-align:center; color: green;" required="Ingresar el estado de la persona.">
                                    <option value="1" selected="">Activo</option>
                                    <!-- <option value="1" required="Seleccione una Opción.">Activo</option> -->
                                    <option value="0" required="Seleccione una Opción.">Inactivo</option>
                                </select>
                                </td> 
                    <?php  
                                }
                            elseif ($dato[14] == 0) 
                                {
                    ?>
                                <td>
                                <select name="estado" style="text-align:center; color: black" required="Ingresar el estado de la persona.">
                                    <option value="0" selected="">Inactivo</option>
                                    <option value="1" required="Seleccione una Opción.">Activo</option>
                                    <!-- <option value="0" required="Seleccione una Opción.">Inactivo</option> -->
                                </select>
                                </td>    
                    <?php    
                                }
                            }
                        }
                    if ($rol == 0)
                        {
                        if ($dato[14] == 1) 
                                {
                    ?>
                                <td>
                                <select name="estado" style="text-align:center; color: green;" required="Ingresar el estado de la persona.">
                                    <option value="1" selected="">Activo</option>
                                    <!-- <option value="1" required="Seleccione una Opción.">Activo</option> -->
                                    <option value="0" required="Seleccione una Opción.">Inactivo</option>
                                </select>
                                </td> 
                    <?php  
                                }
                        elseif ($dato[14] == 0) 
                            {
                    ?>
                            <td>
                            <select name="estado" style="text-align:center; color: black" required="Ingresar el estado de la persona.">
                                <option value="0" selected="">Inactivo</option>
                                <option value="1" required="Seleccione una Opción.">Activo</option>
                                <!-- <option value="0" required="Seleccione una Opción.">Inactivo</option> -->
                            </select>
                            </td>    
                        <?php    
                            }
                        }
                    ?>

                </table>
                    </br>
                <table>
                            <tr>
                            <th style ='color: red;'>Permisos Actuales del Usuario</th>
                            <?php 
                            if ($rol != 0)
                                {
                                if ($dato[15] == 0)
                                    {
                            ?>
                                    <th style ='color: red;'>Nuevos Permisos del Usuario</th>
                            <?php
                                    }
                                elseif($dato[15] != 0)
                                    {
                            ?>
                                    <th style ='color: green;'>Nuevos Permisos del Usuario</th>
                            <?php
                                    }
                                }

                            if ($rol == 0)
                                {
                            ?>
                                    <th style ='color: green;'>Nuevos Permisos del Usuario</th>
                            <?php 
                                }
                            ?>

                            <!--OOOOOOJOOOOOOO aquí está el botón de actualizar, se puso aca por los problemas del FORM y el botón de regresar.-->
                            <th rowspan="2"><button type="submit" class="btn" value="Actualizar">Actualizar</button></th>
                            <th rowspan="2"><a href='javascript:history.back(-1);' title='Ir la página anterior' class="btn" value="Regresar">Regresar</a></th>
                            <!--###############################################################################################################-->
                            </tr>
                                <?php
                                // Muestra todas las posibles combinaciones del CRUD de Permisos de un Usuario loguado actualmente, sobre los otros usuarios registrados en la base de datos.
                                ?>
                                <?php
                                if ($dato[13] == 0) 
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php  
                                    }
                                elseif ($dato[13] == 10)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }                               
                                elseif ($dato[13] == 5)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 15)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 3)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 13)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 8)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 18)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 1)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 11)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                    elseif ($dato[13] == 6)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 16)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 4)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 14)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 9)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                                <?php    
                                    }
                                elseif ($dato[13] == 19)
                                    {
                                ?>
                                    <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                        <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                        <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php    
                                    }

                                // Este Conjunto es el seleecionable por el usuario.
                                
                                if ($rol != 0)
                                    {
                                    if ($dato[15] == 0)
                                        {
                                ?>
                                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                                <?php
                                        }
                                    elseif($dato[15] != 0)
                                        {
                                ?>
                                        <td>
                                        <input type="checkbox" name="permiso_1[]" value="1">Registrar
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="3">Ver
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="5">Editar
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="10">Eliminar      
                                        </td>
                                <?php
                                        }
                                    }

                                if ($rol == 0)
                                    {
                                ?>
                                    <td>
                                        <input type="checkbox" name="permiso_1[]" value="1">Registrar
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="3">Ver
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="5">Editar
                                        <br/>
                                        <input type="checkbox" name="permiso_1[]" value="10">Eliminar      
                                        </td>
                                <?php 
                                    }   
                                // -------------------------------------------------.
                                ?> 
                    </table>
                <?php } ?>       
                </form>
            </div>
            </center>
        </div>
	</section> 	
 <!--Concatenador con la parte Footer. -->
<?php
    require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
    error_reporting(E_ERROR |  E_PARSE);
?>		


