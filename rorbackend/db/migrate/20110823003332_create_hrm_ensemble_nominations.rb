class CreateHrmEnsembleNominations < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_nominations do |t|
      t.integer :person_id
      t.integer :rank_id
      t.date :date_named
      t.date :date_expired
      t.integer :domain_id
      t.integer :subdomain_id
      t.boolean :part_time
      t.boolean :other_institution

      # t.timestamps
    end
  end
end
