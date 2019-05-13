package com.example.android.u_car_ibe;

import android.app.DatePickerDialog;
import android.app.Dialog;
import android.app.DialogFragment;
import android.app.TimePickerDialog;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.nfc.Tag;
import android.support.annotation.NonNull;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.RadioButton;
import android.widget.TextView;
import android.widget.TimePicker;
import android.widget.Toast;

import java.util.Calendar;

public class LoginExitoso extends AppCompatActivity {

    private static final String TAG ="LoginExitoso";
    private Sesiones sesion;
    Button btn_seleccion;
    TextView tvSeleccionado;
    private Button mDisplayDate;
    private DatePickerDialog.OnDateSetListener mDateSetListener;
    RadioButton rb1,rb2;
    RadioButton Cond, Passg;

    private Button eReminderTime;
    private TimePickerDialog.OnTimeSetListener mTimeSetListener;



    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login_exitoso);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);

        mDisplayDate = (Button) findViewById(R.id.tvDate);
        eReminderTime = (Button) findViewById(R.id.Time);

        Cond = (RadioButton) findViewById(R.id.conductor);
        Passg = (RadioButton) findViewById(R.id.pasajero);


        btn_seleccion = (Button)findViewById(R.id.btn_selecccion);
        tvSeleccionado = (TextView)findViewById(R.id.tvRespuesta);
        rb1 = (RadioButton)findViewById(R.id.rb1);
        rb2 = (RadioButton)findViewById(R.id.rb2);


        btn_seleccion.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {


                if(rb1.isChecked() && Passg.isChecked() ){
                    tvSeleccionado.setText(rb1.getText());
                    tvSeleccionado.setText(Passg.getText());
                    ElegirPasajero();

                }else if(rb2.isChecked() && Passg.isChecked()){
                    tvSeleccionado.setText(rb2.getText());
                    tvSeleccionado.setText(Passg.getText());
                    ElegirPasajero();
                }else if(rb1.isChecked() && Cond.isChecked()){
                    tvSeleccionado.setText(rb1.getText());
                    tvSeleccionado.setText(Cond.getText());
                    ElegirConductor();

                }else if(rb2.isChecked() && Cond.isChecked()){
                    tvSeleccionado.setText(rb2.getText());
                    tvSeleccionado.setText(Cond.getText());
                    ElegirConductor();
                }else{
                    tvSeleccionado.setText("No ha seleccionado las opciones necesarias");
                }

            }
        });

        mDisplayDate.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View view) {
                Calendar cal = Calendar.getInstance();
                int year = cal.get(Calendar.YEAR);
                int month = cal.get(Calendar.MONTH);
                int day = cal.get(Calendar.DAY_OF_MONTH);
                DatePickerDialog dialog = new DatePickerDialog(LoginExitoso.this, android.R.style.Theme_Holo_Light_Dialog_MinWidth, mDateSetListener, year, month, day);
                dialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
                dialog.show();
            }
        });
        mDateSetListener = new DatePickerDialog.OnDateSetListener() {
            @Override
            public void onDateSet(DatePicker datePicker, int year, int month, int day) {

                Log.d(TAG, "onDateSet: dd/mm/yyyy: " + day + "/" + month + "/" + year);
                String date = day + "/" + month + "/" + year;
                mDisplayDate.setText(date);
            }
        };

        eReminderTime.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                // TODO Auto-generated method stub
                Calendar mcurrentTime = Calendar.getInstance();
                int hour = mcurrentTime.get(Calendar.HOUR_OF_DAY);
                int minute = mcurrentTime.get(Calendar.MINUTE);
                TimePickerDialog mTimePicker;
                mTimePicker = new TimePickerDialog(LoginExitoso.this, new TimePickerDialog.OnTimeSetListener() {
                    @Override
                    public void onTimeSet(TimePicker timePicker, int hour, int minute) {
                        eReminderTime.setText(hour + ":" + minute);
                    }
                },hour,minute,true);

                mTimePicker.setTitle("Select Time");
                mTimePicker.show();

            }
        });

    }


    public void ElegirConductor(){
        Intent conductor= new Intent(this, GuardarRutaCond.class);
        startActivity(conductor);
        Toast.makeText(this, "Por favor ingresa tu ruta a través de marcadores", Toast.LENGTH_LONG ).show();

    }

    public void ElegirPasajero(){
        Intent pasajero = new Intent(this, GuardarCoordPass.class);
        startActivity(pasajero);
        Toast.makeText(this, "Por favor ingresa un marcador", Toast.LENGTH_LONG ).show();
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







