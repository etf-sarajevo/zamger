class CreateCoreProgrammes < ActiveRecord::Migration
  def change
    create_table :core_programmes do |t|
      t.string :name, :limit => 100
      # t.string :name_en
      t.string :short_name, :limit => 10
      t.integer :final_semester, :default => 0
      t.integer :institution_id, :default => 0
      t.boolean :accepts_students
      t.integer :type_id
      t.integer :precondition
      
      # t.timestamps
    end
  end
end
