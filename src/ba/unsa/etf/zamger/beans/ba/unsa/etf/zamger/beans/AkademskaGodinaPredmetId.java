package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

/**
 * AkademskaGodinaPredmetId generated by hbm2java
 */
public class AkademskaGodinaPredmetId implements java.io.Serializable {

	private int akademskaGodina;
	private int predmet;

	public AkademskaGodinaPredmetId() {
	}

	public AkademskaGodinaPredmetId(int akademskaGodina, int predmet) {
		this.akademskaGodina = akademskaGodina;
		this.predmet = predmet;
	}

	public int getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(int akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public int getPredmet() {
		return this.predmet;
	}

	public void setPredmet(int predmet) {
		this.predmet = predmet;
	}

}
