class CreateHrmEnsembleDomains < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_domains do |t|
      t.integer :institution_id
      t.string :name, :limit => 100

      # t.timestamps
    end
  end
end
