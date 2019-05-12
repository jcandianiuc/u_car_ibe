package com.example.android.u_car_ibe;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

import com.google.android.gms.maps.model.LatLng;

public class Sesiones {
    private SharedPreferences sesion;
    public Sesiones(Context context){
        sesion= PreferenceManager.getDefaultSharedPreferences(context);
    }

    public void guardarUsuario(String usuario){
        sesion.edit().putString("usuario", usuario).apply();
    }

    public void guardarCoord(String latitud, String longitud){
        sesion.edit().putString("latitud", latitud).apply();
        sesion.edit().putString("longitud", longitud).apply();
    }

    public void guardarContraseña(String password){
        sesion.edit().putString("contraseña", password).apply();
    }

    public void ConfirmarRuta(Boolean confirm){
        sesion.edit().putBoolean("ruta", confirm).apply();
    }

   /* public void guardarCorreo(String correo){
        sesion.edit().putString("correo", correo).apply();
    }

    public String obtenerLat(){
        return sesion.getString("latitud", "");
    }

    public String obtenerLong(){
        return sesion.getString("longitud", "");
    }*/

    public String obtenerUsuario(){
        return sesion.getString("usuario", "");
    }

    public Boolean obtenerConfirmRuta(){
        return sesion.getBoolean("ruta", false);
    }

    public String obtenerContraseña(){
        return sesion.getString("contraseña", "");
    }

    public String obtenerCorreo(){
        return sesion.getString("correo", "");
    }

    public void cerrarSesion(){
        sesion.edit().clear().apply();
    }

    public void verificarConn(boolean verify){
        sesion.edit().putBoolean("conexion", verify ).apply();
    }

    public boolean obtenerVerificacionCon(){
        return sesion.getBoolean("conexion", false);
    }
}
