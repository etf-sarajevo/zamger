class CreateHrmEnsembleRanks < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_ranks do |t|
      t.string :name, :limit => 50
      t.string :title, :limit => 10

      # t.timestamps
    end
  end
end
