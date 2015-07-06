package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;

/**
 * Dogadjaj generated by hbm2java
 */
public class Dogadjaj implements java.io.Serializable {

	private Integer id;
	private int predmet;
	private int akademskaGodina;
	private Date datum;
	private int tipDogadjaja;
	private int ispit;
	private Date vrijemeObjave;

	public Dogadjaj() {
	}

	public Dogadjaj(int predmet, int akademskaGodina, Date datum,
			int tipDogadjaja, int ispit, Date vrijemeObjave) {
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.datum = datum;
		this.tipDogadjaja = tipDogadjaja;
		this.ispit = ispit;
		this.vrijemeObjave = vrijemeObjave;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
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

	public Date getDatum() {
		return this.datum;
	}

	public void setDatum(Date datum) {
		this.datum = datum;
	}

	public int getTipDogadjaja() {
		return this.tipDogadjaja;
	}

	public void setTipDogadjaja(int tipDogadjaja) {
		this.tipDogadjaja = tipDogadjaja;
	}

	public int getIspit() {
		return this.ispit;
	}

	public void setIspit(int ispit) {
		this.ispit = ispit;
	}

	public Date getVrijemeObjave() {
		return this.vrijemeObjave;
	}

	public void setVrijemeObjave(Date vrijemeObjave) {
		this.vrijemeObjave = vrijemeObjave;
	}

}
