class CreateCoreScorings < ActiveRecord::Migration
  def change
    create_table :core_scorings do |t|
      t.string :name, :limit => 20
      t.string :options_description, :limit => 100
      
      # t.timestamps
    end
  end
end
