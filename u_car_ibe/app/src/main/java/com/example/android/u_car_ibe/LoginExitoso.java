package com.example.android.u_car_ibe;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

public class LoginExitoso extends AppCompatActivity {
    private Sesiones sesion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login_exitoso);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);

        Button Cond= (Button) findViewById(R.id.conductor);
        Button Passg= (Button) findViewById(R.id.pasajero);

        Cond.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                ElegirConductor();
            }
        });

        Passg.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                ElegirPasajero();
            }
        });


    }

    public void ElegirConductor(){
        Intent conductor= new Intent(this, Conductor.class);
        startActivity(conductor);

    }

    public void ElegirPasajero(){
        Intent pasajero = new Intent(this, Pasajero.class);
        startActivity(pasajero);
    }

    public void cerrarSesionIntent(){
        sesion.cerrarSesion();
        Intent cerrar= new Intent(this, LoginActivity.class);
        startActivity(cerrar);
    }

    public void onBackPressed(){
        super.onBackPressed();
        cerrarSesionIntent();

        //Intent back = new Intent(this, LoginActivity.class);
        //startActivity(back);
        //finish();
    }
}
