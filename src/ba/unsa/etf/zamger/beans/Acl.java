package ba.unsa.etf.zamger.beans;

// Generated May 30, 2015 7:27:23 PM by Hibernate Tools 3.4.0.CR1

/**
 * Acl generated by hbm2java
 */
public class Acl implements java.io.Serializable {

	private AclId id;
	private AclTip aclTip;

	public Acl() {
	}

	public Acl(AclId id, AclTip aclTip) {
		this.id = id;
		this.aclTip = aclTip;
	}

	public AclId getId() {
		return this.id;
	}

	public void setId(AclId id) {
		this.id = id;
	}

	public AclTip getAclTip() {
		return this.aclTip;
	}

	public void setAclTip(AclTip aclTip) {
		this.aclTip = aclTip;
	}

}
