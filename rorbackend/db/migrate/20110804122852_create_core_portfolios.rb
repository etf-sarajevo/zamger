class CreateCorePortfolios < ActiveRecord::Migration
  def change
    create_table :core_portfolios do |t|
      t.integer :student_id
      t.integer :course_offering_id
      
      # t.timestamps
    end
    
    add_index :core_portfolios, [:student_id, :course_offering_id], :unique => true
  end
end
