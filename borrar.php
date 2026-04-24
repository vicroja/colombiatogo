recuperador de conversaciones
* revisa las conversaciones que estén inactivas entre 30 min y 24 horas atrás y que no tengan estado ‘cerrada’. arma un contexto con los ultimos 5 mensajes de interacción y llama a gemini para que determine si la comversación es para cerrar o es para hacer seguimiento y entrega el mensaje de seguimiento (si aplica) para mandarle al ciente. eso debe ir en worker y se correrá por cron cada 5 min.
* cuando se vuelve a escribir se les quita el estado de ‘cerrada’
* debe marcar en los mensajes que el mensaje es de reactivación para poder hacer debug del funcionamiento
