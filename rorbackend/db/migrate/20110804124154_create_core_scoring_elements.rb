class CreateCoreScoringElements < ActiveRecord::Migration
  def change
    create_table :core_scoring_elements do |t|
      t.string :name, :limit => 40
      t.string :gui_name, :limit => 20
      t.string :short_gui_name, :limit => 20
      t.integer :scoring_id
      t.float :max
      t.float :pass
      t.string :option, :limit => 100
      t.boolean :mandatory, :default => false
      

      # t.timestamps
    end
  end
end
