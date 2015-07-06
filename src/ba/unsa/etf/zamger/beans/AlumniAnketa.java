package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;

/**
 * AlumniAnketa generated by hbm2java
 */
public class AlumniAnketa implements java.io.Serializable {

	private Integer id;
	private int osoba;
	private String firma;
	private String radnoMjesto;
	private String telefon;
	private String email;
	private String web;
	private Date zavrsetakStudija;
	private Date prvoZaposlenje;
	private int tipZaposlenja;
	private String treciCiklus;
	private String primjedbeSugestije;

	public AlumniAnketa() {
	}

	public AlumniAnketa(int osoba, String firma, String radnoMjesto,
			String telefon, String email, String web, Date zavrsetakStudija,
			Date prvoZaposlenje, int tipZaposlenja, String treciCiklus,
			String primjedbeSugestije) {
		this.osoba = osoba;
		this.firma = firma;
		this.radnoMjesto = radnoMjesto;
		this.telefon = telefon;
		this.email = email;
		this.web = web;
		this.zavrsetakStudija = zavrsetakStudija;
		this.prvoZaposlenje = prvoZaposlenje;
		this.tipZaposlenja = tipZaposlenja;
		this.treciCiklus = treciCiklus;
		this.primjedbeSugestije = primjedbeSugestije;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public int getOsoba() {
		return this.osoba;
	}

	public void setOsoba(int osoba) {
		this.osoba = osoba;
	}

	public String getFirma() {
		return this.firma;
	}

	public void setFirma(String firma) {
		this.firma = firma;
	}

	public String getRadnoMjesto() {
		return this.radnoMjesto;
	}

	public void setRadnoMjesto(String radnoMjesto) {
		this.radnoMjesto = radnoMjesto;
	}

	public String getTelefon() {
		return this.telefon;
	}

	public void setTelefon(String telefon) {
		this.telefon = telefon;
	}

	public String getEmail() {
		return this.email;
	}

	public void setEmail(String email) {
		this.email = email;
	}

	public String getWeb() {
		return this.web;
	}

	public void setWeb(String web) {
		this.web = web;
	}

	public Date getZavrsetakStudija() {
		return this.zavrsetakStudija;
	}

	public void setZavrsetakStudija(Date zavrsetakStudija) {
		this.zavrsetakStudija = zavrsetakStudija;
	}

	public Date getPrvoZaposlenje() {
		return this.prvoZaposlenje;
	}

	public void setPrvoZaposlenje(Date prvoZaposlenje) {
		this.prvoZaposlenje = prvoZaposlenje;
	}

	public int getTipZaposlenja() {
		return this.tipZaposlenja;
	}

	public void setTipZaposlenja(int tipZaposlenja) {
		this.tipZaposlenja = tipZaposlenja;
	}

	public String getTreciCiklus() {
		return this.treciCiklus;
	}

	public void setTreciCiklus(String treciCiklus) {
		this.treciCiklus = treciCiklus;
	}

	public String getPrimjedbeSugestije() {
		return this.primjedbeSugestije;
	}

	public void setPrimjedbeSugestije(String primjedbeSugestije) {
		this.primjedbeSugestije = primjedbeSugestije;
	}

}
