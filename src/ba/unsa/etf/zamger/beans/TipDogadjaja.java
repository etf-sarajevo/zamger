package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * TipDogadjaja generated by hbm2java
 */
public class TipDogadjaja implements java.io.Serializable {

	private Integer id;
	private String naziv;
	private int predmet;
	private int akademskaGodina;

	public TipDogadjaja() {
	}

	public TipDogadjaja(String naziv, int predmet, int akademskaGodina) {
		this.naziv = naziv;
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public int getPredmet() {
		return this.predmet;
	}

	public void setPredmet(int predmet) {
		this.predmet = predmet;
	}

	public int getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(int akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

}
