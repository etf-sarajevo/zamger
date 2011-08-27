class CreateHrmEnsembleSubdomains < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_subdomains do |t|
      t.integer :domain_id
      t.string :name, :limit => 100

      t.timestamps
    end
  end
end
