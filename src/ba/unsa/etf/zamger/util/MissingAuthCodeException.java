package ba.unsa.etf.zamger.util;

public class MissingAuthCodeException extends Exception {

    public MissingAuthCodeException(){
        super();
    }

    public MissingAuthCodeException(String message){
        super(message);
    }
}
