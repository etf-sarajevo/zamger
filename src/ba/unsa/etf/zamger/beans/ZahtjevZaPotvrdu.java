package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;

/**
 * ZahtjevZaPotvrdu generated by hbm2java
 */
public class ZahtjevZaPotvrdu implements java.io.Serializable {

	private Integer id;
	private Integer student;
	private Integer tipPotvrde;
	private Integer svrhaPotvrde;
	private Date datumZahtjeva;
	private Integer status;

	public ZahtjevZaPotvrdu() {
	}

	public ZahtjevZaPotvrdu(Integer student, Integer tipPotvrde,
			Integer svrhaPotvrde, Date datumZahtjeva, Integer status) {
		this.student = student;
		this.tipPotvrde = tipPotvrde;
		this.svrhaPotvrde = svrhaPotvrde;
		this.datumZahtjeva = datumZahtjeva;
		this.status = status;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public Integer getStudent() {
		return this.student;
	}

	public void setStudent(Integer student) {
		this.student = student;
	}

	public Integer getTipPotvrde() {
		return this.tipPotvrde;
	}

	public void setTipPotvrde(Integer tipPotvrde) {
		this.tipPotvrde = tipPotvrde;
	}

	public Integer getSvrhaPotvrde() {
		return this.svrhaPotvrde;
	}

	public void setSvrhaPotvrde(Integer svrhaPotvrde) {
		this.svrhaPotvrde = svrhaPotvrde;
	}

	public Date getDatumZahtjeva() {
		return this.datumZahtjeva;
	}

	public void setDatumZahtjeva(Date datumZahtjeva) {
		this.datumZahtjeva = datumZahtjeva;
	}

	public Integer getStatus() {
		return this.status;
	}

	public void setStatus(Integer status) {
		this.status = status;
	}

}
