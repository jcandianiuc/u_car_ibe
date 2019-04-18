package com.example.android.u_car_ibe;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;

public class LoginExitoso extends AppCompatActivity {
    private Sesiones sesion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login_exitoso);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);
        cerrarSesionIntent();
    }

    public void cerrarSesionIntent(){
        sesion.cerrarSesion();
        Intent cerrar= new Intent(this, LoginActivity.class);
        startActivity(cerrar);
    }
}
