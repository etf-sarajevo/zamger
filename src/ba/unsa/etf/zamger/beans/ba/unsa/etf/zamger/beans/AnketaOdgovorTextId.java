package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

/**
 * AnketaOdgovorTextId generated by hbm2java
 */
public class AnketaOdgovorTextId implements java.io.Serializable {

	private int rezultat;
	private int pitanje;

	public AnketaOdgovorTextId() {
	}

	public AnketaOdgovorTextId(int rezultat, int pitanje) {
		this.rezultat = rezultat;
		this.pitanje = pitanje;
	}

	public int getRezultat() {
		return this.rezultat;
	}

	public void setRezultat(int rezultat) {
		this.rezultat = rezultat;
	}

	public int getPitanje() {
		return this.pitanje;
	}

	public void setPitanje(int pitanje) {
		this.pitanje = pitanje;
	}

}
